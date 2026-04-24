<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index($productId)
    {
        return response()->json(Review::where('product_id', $productId)->with('user')->latest()->get());
    }

    public function store(Request $request, $productId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment']
        ]);

        return response()->json($review, 201);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        if ($review->user_id !== auth()->id() && !auth()->user()->isAdmin()) { // Allow owner or admin
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        $review->delete();
        return response()->json(null, 204);
    }
}
