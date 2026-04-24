<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard() {
        $stats = [
            'total_sales' => Order::where('status', 'paid')->sum('total_price'),
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_users' => User::count(),
            'low_stock_products' => Product::where('stock', '<', 10)->get(),
            'recent_orders' => Order::with('user')->latest()->take(5)->get()
        ];
        return response()->json($stats);
    }
}
