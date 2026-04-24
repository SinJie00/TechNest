<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images', 'reviews', 'variants', 'brand']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('brand', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Storage/Size Filter
        if ($request->filled('storage')) {
            $storageValues = is_array($request->storage) ? $request->storage : explode(',', $request->storage);
            $query->whereHas('variants', function($q) use ($storageValues) {
                $q->where(function($q2) use ($storageValues) {
                    foreach ($storageValues as $val) {
                        // We check the attributes json column which contains {Size: "..."}
                        $q2->orWhere('attributes->Size', $val);
                    }
                });
            });
        }

        // Color Filter
        if ($request->filled('colors')) {
            $colorValues = is_array($request->colors) ? $request->colors : explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorValues) {
                $q->where(function($q2) use ($colorValues) {
                    foreach ($colorValues as $val) {
                        $q2->orWhere('attributes->Color', $val);
                    }
                });
            });
        }

        // Sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Duplicate query for facets calculation to get all possible filters without pagination
        $facetQuery = clone $query;
        $allMatchingProducts = $facetQuery->get();
        $availableSizes = [];
        $availableColors = [];

        foreach($allMatchingProducts as $p) {
            if ($p->variants) {
                foreach($p->variants as $v) {
                    if ($v->attributes) {
                        if (isset($v->attributes['Size'])) $availableSizes[] = $v->attributes['Size'];
                        if (isset($v->attributes['Color'])) $availableColors[] = $v->attributes['Color'];
                    }
                }
            }
        }

        $products = $query->paginate(12);

        $response = $products->toArray();
        $response['facets'] = [
            'sizes' => array_values(array_unique($availableSizes)),
            'colors' => array_values(array_unique($availableColors))
        ];

        return response()->json($response);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'variants', 'reviews.user'])
            ->findOrFail($id);
            
        return response()->json($product);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $products = Product::with('variants:id,product_id,price,discount_price')->where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'price', 'discount_price') // optimziation
            ->take(10)
            ->get();

        return response()->json($products);
    }
}
