<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(auth()->user()->orders()->with(['items.product.images', 'items.variant'])->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|min:8|max:20',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|in:Malaysia',
            'use_shipping_for_billing' => 'boolean',
            'billing_address' => 'exclude_if:use_shipping_for_billing,true|required|string|max:255',
            'billing_city' => 'exclude_if:use_shipping_for_billing,true|required|string|max:255',
            'billing_state' => 'exclude_if:use_shipping_for_billing,true|required|string|max:255',
            'billing_postal_code' => 'exclude_if:use_shipping_for_billing,true|required|string|max:20',
            'billing_country' => 'exclude_if:use_shipping_for_billing,true|required|in:Malaysia',
            'shipping_method' => 'required|in:home_delivery,store_pickup',
            'stripeToken' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // Payment creation moved below Stripe logic
        ]);

        return \DB::transaction(function() use ($request) {
            $user = auth('sanctum')->user();
            $shipping_fee = $request->shipping_method === 'home_delivery' ? 10 : 0;
            $total = $shipping_fee;
            
            $full_shipping = "{$request->shipping_address}, {$request->shipping_city}, {$request->shipping_state} {$request->shipping_postal_code}, {$request->shipping_country}";
            
            $full_billing = $request->use_shipping_for_billing 
                ? $full_shipping 
                : "{$request->billing_address}, {$request->billing_city}, {$request->billing_state} {$request->billing_postal_code}, {$request->billing_country}";

            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'total_price' => 0,
                'status' => 'pending',
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'shipping_address' => $full_shipping,
                'billing_address' => $full_billing,
                'shipping_method' => $request->shipping_method,
                'shipping_fee' => $shipping_fee
            ]);

            $cart = session()->get('cart', []);
            foreach($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                $variant = isset($item['variant_id']) ? \App\Models\ProductVariant::lockForUpdate()->find($item['variant_id']) : null;

                $attributes = $variant ? (is_string($variant->attributes) ? json_decode($variant->attributes, true) : $variant->attributes) : null;
                $variantName = $attributes && is_array($attributes) ? implode(', ', $attributes) : '';
                $fullName = $product->name . ($variantName ? ' (' . $variantName . ')' : '');
                
                $key = $item['product_id'] . '-' . ($item['variant_id'] ?? 'novar');

                if ($variant) {
                    if ($variant->stock <= 0) {
                        abort(400, "Sorry, this product is now out of stock.");
                    }
                    if ($variant->stock < $item['quantity']) {
                        $unitWord = $variant->stock == 1 ? 'unit' : 'units';
                        abort(400, "Stock changed, only {$variant->stock} {$unitWord} available.");
                    }
                    
                    $price = $variant->discount_price !== null ? $variant->discount_price : $variant->price;
                    $variant->decrement('stock', $item['quantity']);
                } else {
                    if ($product->stock <= 0) {
                        abort(400, "Sorry, this product is now out of stock.");
                    }
                    if ($product->stock < $item['quantity']) {
                        $unitWord = $product->stock == 1 ? 'unit' : 'units';
                        abort(400, "Stock changed, only {$product->stock} {$unitWord} available.");
                    }
                    $price = $product->final_price;
                    $product->decrement('stock', $item['quantity']);
                }
                
                $lineTotal = $price * $item['quantity'];
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $price
                ]);
            }

            $order->update(['total_price' => $total]);

            // Try charging via Stripe
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $charge = \Stripe\Charge::create([
                    'amount' => intval(round($total * 100)), // Cents
                    'currency' => 'myr',
                    'description' => 'TechNest Order - ' . $request->contact_email,
                    'source' => $request->stripeToken,
                ]);

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'card',
                    'amount' => $total,
                    'status' => 'success',
                    'transaction_id' => $charge->id
                ]);
            } catch(\Stripe\Exception\CardException $e) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['payment_method' => [$e->getError()->message]]
                ], 422)->throwResponse();
            } catch(\Exception $e) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['payment_method' => ['Payment processing failed. Please try again.']]
                ], 422)->throwResponse();
            }

            // Update order status
            $order->update(['status' => 'paid']);

            // Clear Cart Session immediately upon successful Payment since transaction will definitely commit
            session()->forget('cart');

            // Send confirmation email
            \Illuminate\Support\Facades\Mail::to($order->contact_email)->send(new \App\Mail\OrderConfirmationMail($order));

            return response()->json($order->load('items.product', 'payment'), 201);
        });
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'payment')->where('user_id', auth()->id())->findOrFail($id);
        return response()->json($order);
    }
    
    // Admin methods
    public function adminView() {
        return view('admin.orders');
    }

    public function adminIndex() {
        return response()->json(Order::with('user', 'items.product', 'items.variant')->latest()->paginate(20));
    }
    
    public function updateStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return response()->json($order);
    }
}
