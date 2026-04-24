<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index($productId)
    {
        return response()->json(\App\Models\ProductVariant::where('product_id', $productId)->get());
    }

    public function store(Request $request, $productId)
    {
        $validated = $request->validate([
            'variant_name' => 'required|string',
            'variant_value' => 'required|string',
            'additional_price' => 'nullable|numeric'
        ]);
        
        $variant = \App\Models\ProductVariant::create(array_merge($validated, ['product_id' => $productId]));
        return response()->json($variant, 201);
    }

    public function update(Request $request, $id)
    {
        $variant = \App\Models\ProductVariant::findOrFail($id);
        $variant->update($request->all());
        return response()->json($variant);
    }

    public function destroy($id)
    {
        \App\Models\ProductVariant::destroy($id);
        return response()->json(null, 204);
    }
}
