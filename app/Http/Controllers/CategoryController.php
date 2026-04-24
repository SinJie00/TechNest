<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        // Return top-level categories with subcategories
        $categories = Category::with('subcategories')->get();
        // Standardize the key name to 'children' for consistency in frontend if needed, 
        // or just update frontend to use 'subcategories'. 
        // Let's iterate and add children property for easy compatibility with frontend logic.
        foreach($categories as $cat) {
            $cat->children = $cat->subcategories;
        }
        return response()->json($categories);
    }

    public function all() {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::with('children', 'parent')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        Category::destroy($id);
        return response()->json(null, 204);
    }
}
