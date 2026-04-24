<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        // For API (sanctum authenticated) or Session
        // If API:
        // return response()->json(auth()->user()->startCart...);
        // For simplicity using session for guest / db for auth if needed.
        // But requirement says "Cart must be implemented as a popup...".
        // Let's assume frontend manages cart state or session.
        // API approach:
        return response()->json(session()->get('cart', []));
    }

    public function add(Request $request)
    {
        $validation = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);
        $id = $request->product_id;
        $variantId = $request->variant_id;
        $key = $id . '-' . ($variantId ?? 'novar');

        $product = Product::find($id);
        $variant = $variantId ? \App\Models\ProductVariant::find($variantId) : null;
        
        $stock = $variant ? $variant->stock : $product->stock;
        
        $attributes = $variant ? (is_string($variant->attributes) ? json_decode($variant->attributes, true) : $variant->attributes) : null;
        $variantName = $attributes && is_array($attributes) ? implode(', ', $attributes) : '';
        $fullName = $product->name . ($variantName ? ' (' . $variantName . ')' : '');

        $currentQuantity = isset($cart[$key]) ? $cart[$key]['quantity'] : 0;
        $newQuantity = $currentQuantity + $request->quantity;
        
        $warning = null;
        if ($newQuantity > $stock) {
            $added = $stock - $currentQuantity;
            if ($stock <= 0) {
                return response()->json(['message' => 'Out of stock', 'error' => "{$fullName} is currently out of stock."], 400);
            }
            if ($added <= 0) {
                 return response()->json(['message' => 'Stock limit reached', 'error' => "You already have the maximum available stock for {$fullName} in your cart."], 400);
            }
            $newQuantity = $stock;
            $warning = "Only {$stock} units of {$fullName} are left. Your cart has been updated to the maximum available stock.";
        }

        if(isset($cart[$key])) {
            $cart[$key]['quantity'] = $newQuantity;
            $cart[$key]['max_stock'] = $stock;
            if ($warning) $cart[$key]['warning'] = $warning;
        } else {
             $price = $product->final_price;
             $image = $product->images->first()?->image_url;
             
             if ($variant) {
                 $price = $variant->discount_price !== null ? $variant->discount_price : $variant->price;
                 $imagesAttribute = is_string($variant->images) ? json_decode($variant->images, true) : $variant->images;
                 if ($imagesAttribute && is_array($imagesAttribute) && count($imagesAttribute) > 0) {
                     $image = '/storage/' . $imagesAttribute[0];
                 }
             }

             $cart[$key] = [
                "product_id" => $product->id,
                "name" => $product->name,
                "variant_name" => $variantName,
                "price" => $price,
                "quantity" => $newQuantity,
                "variant_id" => $variantId,
                "image" => $image,
                "warning" => $warning,
                "max_stock" => $stock
             ];
        }

        session()->put('cart', $cart);
        
        $response = ['message' => 'Added to cart', 'cart' => $cart];
        if ($warning) {
             $response['warning'] = $warning;
        }
        
        return response()->json($response);
    }

    public function update(Request $request) {
        $key = $request->key;
        $quantity = $request->quantity;
        $cart = session()->get('cart', []);
        
        if(isset($cart[$key])) {
            $item = $cart[$key];
            $product = Product::find($item['product_id']);
            $variant = $item['variant_id'] ? \App\Models\ProductVariant::find($item['variant_id']) : null;
            $stock = $variant ? $variant->stock : $product->stock;
            
            if ($quantity > $stock) {
                $quantity = $stock;
                $cart[$key]['warning'] = "Stock changed, only {$stock} units left.";
            } else {
                unset($cart[$key]['warning']);
            }
            if ($quantity < 1) {
                $quantity = 1;
            }
            $cart[$key]['quantity'] = $quantity;
            $cart[$key]['max_stock'] = $stock;
            session()->put('cart', $cart);
        }
        return response()->json($cart);
    }

    public function remove(Request $request) {
        $key = $request->key;
        $cart = session()->get('cart', []);
        if(isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }
        return response()->json(['message' => 'Item removed', 'cart' => $cart]);
    }
}
