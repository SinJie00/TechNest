<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'subcategory', 'brand', 'variants'])->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::with('subcategories')->get();
        $brands = Brand::all();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $hasVariants = $request->boolean('has_variants', false);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'brand_id' => 'required|exists:brands,id',
            'status' => 'boolean',
            
            // Main images required
            'images' => $hasVariants ? 'nullable|array' : 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            
            'price' => $hasVariants ? 'nullable' : 'required|numeric|min:0',
            'discount_price' => $hasVariants ? 'nullable' : 'nullable|numeric|min:0|lt:price',
            'stock' => $hasVariants ? 'nullable' : 'required|integer|min:0',

            'options' => $hasVariants ? 'required|array' : 'nullable|array',
            'variants' => $hasVariants ? 'required|array' : 'nullable|array',
            'variants.*.sku' => 'required_if:has_variants,true|distinct|unique:product_variants,sku',
            'variants.*.price' => 'required_if:has_variants,true|numeric|min:0',
            // Removed lt:variants.*.price rule, handled manually
            'variants.*.discount_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_if:has_variants,true|integer|min:0',
        ], [], [
            'images.*' => 'Main Product Image',
            'variants.*.images.*' => 'Variant Image',
            'variants.*.sku' => 'Variant SKU',
            'variants.*.price' => 'Variant Price',
            'variants.*.stock' => 'Variant Stock',
        ]);

        if ($hasVariants && $request->has('variants')) {
            foreach ($request->variants as $index => $variant) {
                 if (isset($variant['price']) && isset($variant['discount_price']) && $variant['discount_price'] !== null) {
                     if ((float)$variant['discount_price'] >= (float)$variant['price']) {
                         return back()->withErrors(['variants' => 'Discount price must be less than price for variant ' . ($index + 1) . '.'])->withInput();
                     }
                 }
            }
        }

        // Custom Validation for Required Option Images
        if ($hasVariants && $request->has('options')) {
            $optImages = $request->file('option_images', []);
            foreach ($request->options as $option) {
                if (isset($option['requires_image']) && $option['requires_image']) {
                    $optName = $option['name'];
                    $values = array_map('trim', explode(',', $option['values']));
                    foreach ($values as $val) {
                        if (!$val) continue;
                        
                        // Robust check: direct array access instead of $request->hasFile() dot-notation
                        $hasFile = isset($optImages[$optName][$val]) && !empty($optImages[$optName][$val]);
                        
                        if (!$hasFile) {
                             return back()->withErrors(['option_images' => "Images are required for $optName: $val"])->withInput();
                        }
                    }
                }
            }
        }

        $productData = $request->except(['images', 'variants', 'options', 'has_variants', 'option_images']);
        if ($hasVariants) {
            $prices = collect($request->variants)->map(function($v) { 
                return isset($v['discount_price']) && $v['discount_price'] !== null ? $v['discount_price'] : ($v['price'] ?? 0); 
            });
            $productData['price'] = $prices->min() ?? 0;
            $productData['stock'] = collect($request->variants)->sum('stock') ?? 0;
            $productData['discount_price'] = null;
            $productData['options'] = $request->options; // Store options definition
        }

        $product = Product::create($productData);

    // Handle Product Images with Order
    $imageOrder = $request->input('image_order', []);
    $imageFiles = $request->file('images', []);

    if (!empty($imageOrder)) {
        foreach ($imageOrder as $index => $orderValue) {
            if (strpos($orderValue, 'new:') === 0) {
                $fileIndex = (int)str_replace('new:', '', $orderValue);
                if (isset($imageFiles[$fileIndex])) {
                    $image = $imageFiles[$fileIndex];
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $path,
                        'alt_text' => $product->name,
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    } elseif (!empty($imageFiles)) {
        // Fallback for cases where order might not be sent (e.g. standard file input)
        foreach ($imageFiles as $index => $image) {
            $path = $image->store('products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => $path,
                'alt_text' => $product->name,
                'sort_order' => $index,
            ]);
        }
    }

    // Handle Variants with Ordered Images
    if ($hasVariants && $request->has('variants')) {
        
        // 1. Store NEW Option Image Uploads with preserved indices
        $uploadedOptImages = $request->file('option_images', []);
        $storedPaths = []; // Store paths indexed by [optName][val][fileIndex]
        
        if (!empty($uploadedOptImages)) {
            foreach ($uploadedOptImages as $optName => $vals) {
                foreach ($vals as $val => $files) {
                    foreach ($files as $fileIdx => $file) {
                        $storedPaths[$optName][$val][$fileIdx] = $file->store('variants', 'public');
                    }
                }
            }
        }

        // 2. Build Ordered Image Paths for Attribute Groups
        $groupedPaths = [];
        $optionImageOrder = $request->input('option_image_order', []);

        if (!empty($optionImageOrder)) {
            foreach ($optionImageOrder as $optName => $vals) {
                foreach ($vals as $val => $orders) {
                    $paths = [];
                    foreach ($orders as $orderValue) {
                        if (strpos($orderValue, 'new:') === 0) {
                            $fileIdx = (int)str_replace('new:', '', $orderValue);
                            if (isset($storedPaths[$optName][$val][$fileIdx])) {
                                $paths[] = $storedPaths[$optName][$val][$fileIdx];
                            }
                        }
                    }
                    $groupedPaths[$optName][$val] = $paths;
                }
            }
        }

        // 3. Fallback: Add any uploaded images not in the order list
        if (!empty($storedPaths)) {
            foreach ($storedPaths as $optName => $vals) {
                foreach ($vals as $val => $paths) {
                    if (!isset($groupedPaths[$optName][$val])) {
                        $groupedPaths[$optName][$val] = array_values($paths);
                    }
                }
            }
        }

        // 3b. Get options that require images
        $requiresImageOptions = [];
        if ($request->has('options')) {
            foreach ($request->options as $opt) {
                if (!empty($opt['requires_image'])) {
                    $requiresImageOptions[] = $opt['name'];
                }
            }
        }

        // 4. Create Variants
        foreach ($request->variants as $index => $variantData) {
            $attributes = isset($variantData['attributes']) ? json_decode($variantData['attributes'], true) : [];
            
            // Collect images ONLY from attributes that require images
            $variantImages = [];
            foreach ($attributes as $attrName => $attrValue) {
                if (in_array($attrName, $requiresImageOptions) && isset($groupedPaths[$attrName][$attrValue])) {
                    $variantImages = array_merge($variantImages, $groupedPaths[$attrName][$attrValue]);
                }
            }

            // Per-variant specific uploads if any
            if ($request->hasFile("variants.{$index}.images")) {
                 foreach ($request->file("variants.{$index}.images") as $image) {
                    $variantImages[] = $image->store('variants', 'public');
                 }
            }
            
            ProductVariant::create([
                'product_id' => $product->id,
                'attributes' => $attributes,
                'sku' => $variantData['sku'],
                'price' => $variantData['price'] ?? 0,
                'discount_price' => $variantData['discount_price'] ?? null,
                'stock' => $variantData['stock'] ?? 0,
                'status' => isset($variantData['status']) ? (bool)$variantData['status'] : true,
                'is_default' => isset($variantData['is_default']) ? (bool)$variantData['is_default'] : false,
                'images' => array_values(array_unique($variantImages)) 
            ]);
        }
        
        // Auto cover logic
        $coverImage = null;
        $vList = $product->variants()->get();
        $defaultVariant = $vList->where('is_default', 1)->first();
        if ($defaultVariant && !empty($defaultVariant->images)) $coverImage = $defaultVariant->images[0];
        else if ($inStockVariant = $vList->where('stock', '>', 0)->first()) {
             if (!empty($inStockVariant->images)) $coverImage = $inStockVariant->images[0];
        } else if ($firstVariant = $vList->first()) {
             if (!empty($firstVariant->images)) $coverImage = $firstVariant->images[0];
        }
        
        if ($coverImage) {
            ProductImage::create(['product_id' => $product->id, 'image_url' => $coverImage, 'alt_text' => $product->name, 'sort_order' => -1]);
        }
    }

        // ... existing code ...
        
        $total = Product::count();
        $lastPage = ceil($total / 10);

        return redirect()->route('admin.products.index', ['page' => $lastPage])->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with(['images' => function($q) {
            $q->where('sort_order', '>=', 0)->orderBy('sort_order')->orderBy('id');
        }, 'variants'])->findOrFail($id);
        $categories = Category::with('subcategories')->get();
        $brands = Brand::all();
        // Determine initial hasVariants state for the view
        $hasVariants = $product->variants()->exists() || !empty($product->options);
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'hasVariants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $hasVariants = $request->boolean('has_variants', false);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'brand_id' => 'required|exists:brands,id',
            'status' => 'boolean',
            'images' => $hasVariants ? 'nullable|array' : 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            
            'price' => $hasVariants ? 'nullable' : 'required|numeric|min:0',
            'discount_price' => $hasVariants ? 'nullable' : 'nullable|numeric|min:0|lt:price',
            'stock' => $hasVariants ? 'nullable' : 'required|integer|min:0',

            'options' => $hasVariants ? 'required|array' : 'nullable|array',
            'variants' => $hasVariants ? 'required|array' : 'nullable|array',
            'variants.*.sku' => 'required_if:has_variants,true|distinct', 
            // Unique check needs to be more complex to ignore current ID, effectively needs custom rule or loop check if strict uniqueness required.
            // For simplicity/speed in this context: we'll trust distinct+logic below or catch DB error if we cared, but let's stick to simple required/distinct for array
            
            'variants.*.price' => 'required_if:has_variants,true|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_if:has_variants,true|integer|min:0',
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [], [
            'images.*' => 'Main Product Image',
            'variants.*.images.*' => 'Variant Image',
            'variants.*.sku' => 'Variant SKU',
            'variants.*.price' => 'Variant Price',
            'variants.*.stock' => 'Variant Stock',
        ]);

        if ($hasVariants && $request->has('variants')) {
             foreach ($request->variants as $index => $variant) {
                 if (isset($variant['price']) && isset($variant['discount_price']) && $variant['discount_price'] !== null) {
                      if ((float)$variant['discount_price'] >= (float)$variant['price']) {
                          return back()->withErrors(['variants' => 'Discount price must be less than price for variant ' . ($index + 1) . '.'])->withInput();
                      }
                 }
             }
        }

        $product = Product::findOrFail($id);

        // 1. Validate Main Images (Existing count - Deleted count + New count > 0)
        $existingCount = $product->images()->count();
        $deletedCount = $request->has('deleted_images') ? count($request->deleted_images) : 0;
        $newCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        
        if (!$hasVariants && ($existingCount - $deletedCount + $newCount) <= 0) {
            return back()->withErrors(['images' => 'At least one product image is required.'])->withInput();
        }

        // 2. Validate Option Images (If Required)
        if ($hasVariants && $request->has('options')) {
            $uploadedOptImages = $request->file('option_images', []);
            foreach ($request->options as $option) {
                if (isset($option['requires_image']) && $option['requires_image']) {
                    $optName = $option['name'];
                    $values = array_map('trim', explode(',', $option['values']));
                    
                    foreach ($values as $val) {
                        if (!$val) continue;
                        
                        // Check if new images uploaded (direct array check)
                        $hasNew = isset($uploadedOptImages[$optName][$val]) && !empty($uploadedOptImages[$optName][$val]);
                        
                        if (!$hasNew) {
                             // Check DB for variants with this attribute value
                             $variantsWithValue = $product->variants->filter(function($v) use ($optName, $val) {
                                 return ($v->attributes[$optName] ?? '') == $val;
                             });
                             
                             if ($variantsWithValue->isEmpty()) {
                                 // New Option Value -> Must have image
                                 return back()->withErrors(['option_images' => "Images are required for new option value: $optName - $val"])->withInput();
                             }
                             
                             // Check if any has images (assuming they share, one having is valid enough)
                             $hasImages = $variantsWithValue->contains(function($v) {
                                 return !empty($v->images);
                             });
                             
                             if (!$hasImages) {
                                 return back()->withErrors(['option_images' => "Images are required for $optName: $val"])->withInput();
                             }
                        }
                    }
                }
            }
        }
        
        $productData = $request->except(['images', 'variants', 'deleted_images', 'has_variants', 'options', 'option_images', 'image_order', 'mixed_order']);
        if ($hasVariants) {
            $prices = collect($request->variants)->map(function($v) { 
                return isset($v['discount_price']) && $v['discount_price'] !== null ? $v['discount_price'] : ($v['price'] ?? 0); 
            });
            $productData['price'] = $prices->min() ?? 0;
            $productData['stock'] = collect($request->variants)->sum('stock') ?? 0;
            $productData['discount_price'] = null;
            $productData['options'] = $request->options; 
        } else {
            // clear options if switching to no variants?
            $productData['options'] = null;
        }
        
        $product->update($productData);

        // Handle Deleted Product Images First
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image && $image->product_id == $product->id) {
                    if (Storage::disk('public')->exists($image->image_url)) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                    $image->delete();
                }
            }
        }

        // Handle Product Images with Order
        $imageOrder = $request->input('image_order', []);
        $newFiles = $request->file('images', []);

        if (!empty($imageOrder)) {
            foreach ($imageOrder as $index => $orderValue) {
                if (strpos($orderValue, 'existing:') === 0) {
                    $id = (int)str_replace('existing:', '', $orderValue);
                    ProductImage::where('id', $id)->where('product_id', $product->id)->update(['sort_order' => $index]);
                } elseif (strpos($orderValue, 'new:') === 0) {
                    $fileIdx = (int)str_replace('new:', '', $orderValue);
                    if (isset($newFiles[$fileIdx])) {
                        $image = $newFiles[$fileIdx];
                        $path = $image->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $path,
                            'alt_text' => $product->name,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        } elseif (!empty($newFiles)) {
            $existingCount = $product->images()->count();
            foreach ($newFiles as $index => $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $path,
                    'alt_text' => $product->name,
                    'sort_order' => $existingCount + $index,
                ]);
            }
        }

        // Handle Variants Sync
        if (!$hasVariants) {
            // Delete all variants if switched to simple product
            foreach ($product->variants as $variant) {
                 if ($variant->images) {
                    foreach ($variant->images as $file) Storage::disk('public')->delete($file);
                 }
                 $variant->delete();
            }
        } else {
            // 0. Get deleted grouped image paths
            $deletedGroupedPaths = $request->input('deleted_grouped_images', []);
            
            // Delete the actual files from storage
            foreach ($deletedGroupedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            
            // 1. Store NEW Option Image Uploads with preserved indices
            $uploadedOptImages = $request->file('option_images', []);
            $storedPaths = []; // Store paths indexed by [optName][val][fileIndex]
            
            if (!empty($uploadedOptImages)) {
                foreach ($uploadedOptImages as $optName => $vals) {
                    foreach ($vals as $val => $files) {
                        foreach ($files as $fileIdx => $file) {
                            $storedPaths[$optName][$val][$fileIdx] = $file->store('variants', 'public');
                        }
                    }
                }
            }

            // 2. Build Precise Ordered Paths for each Attribute Group
            $optionImageOrder = $request->input('option_image_order', []);
            $groupedPaths = [];
            
            if (!empty($optionImageOrder)) {
                 foreach ($optionImageOrder as $optName => $vals) {
                     foreach ($vals as $val => $orders) {
                         $paths = [];
                         foreach ($orders as $orderValue) {
                             if (strpos($orderValue, 'existing:') === 0) {
                                 $existingPath = str_replace('existing:', '', $orderValue);
                                 // Only include if not in deleted list
                                 if (!in_array($existingPath, $deletedGroupedPaths)) {
                                     $paths[] = $existingPath;
                                 }
                             } elseif (strpos($orderValue, 'new:') === 0) {
                                 $fileIdx = (int)str_replace('new:', '', $orderValue);
                                 if (isset($storedPaths[$optName][$val][$fileIdx])) {
                                     $paths[] = $storedPaths[$optName][$val][$fileIdx];
                                 }
                             }
                         }
                         $groupedPaths[$optName][$val] = $paths;
                     }
                 }
            }
            
            // 2b. Get options that require images
            $requiresImageOptions = [];
            if ($request->has('options')) {
                foreach ($request->options as $opt) {
                    if (!empty($opt['requires_image'])) {
                        $requiresImageOptions[] = $opt['name'];
                    }
                }
            }
            
            // 2c. Fallback: Get existing images from variants if no order provided for that group
            // ONLY for options that require images
            foreach ($product->variants as $v) {
                if (!empty($v->images)) {
                    foreach ($v->attributes as $attrName => $attrValue) {
                        // Only assign fallback for options that require images
                        if (in_array($attrName, $requiresImageOptions) && !isset($groupedPaths[$attrName][$attrValue])) {
                            // Filter out deleted images
                            $groupedPaths[$attrName][$attrValue] = array_values(
                                array_diff($v->images, $deletedGroupedPaths)
                            );
                        }
                    }
                }
            }

            // 3. Sync Variants with Precise Images
            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $submittedVariantIds = [];

            if ($request->has('variants')) {
                foreach ($request->variants as $index => $variantData) {
                    $attributes = isset($variantData['attributes']) ? json_decode($variantData['attributes'], true) : [];
                    
                    // Collect images ONLY from attributes that require images
                    $variantImages = [];
                    foreach ($attributes as $attrName => $attrValue) {
                        if (in_array($attrName, $requiresImageOptions) && isset($groupedPaths[$attrName][$attrValue])) {
                            $variantImages = array_merge($variantImages, $groupedPaths[$attrName][$attrValue]);
                        }
                    }

                    $data = [
                        'attributes' => $attributes,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'] ?? 0,
                        'discount_price' => $variantData['discount_price'] ?? null,
                        'stock' => $variantData['stock'] ?? 0,
                        'status' => isset($variantData['status']) ? (bool)$variantData['status'] : true,
                        'is_default' => isset($variantData['is_default']) ? (bool)$variantData['is_default'] : false,
                        'images' => array_values(array_unique($variantImages))
                    ];

                    if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update($data);
                        $submittedVariantIds[] = $variantData['id'];
                    } else {
                        $data['product_id'] = $product->id;
                        $v = ProductVariant::create($data);
                        $submittedVariantIds[] = $v->id;
                    }
                }
            }
            
            // Delete removed variants
            $toDelete = array_diff($existingVariantIds, $submittedVariantIds);
            foreach ($toDelete as $delId) {
                $v = ProductVariant::find($delId);
                if ($v) {
                    if ($v->images) {
                        foreach ($v->images as $img) Storage::disk('public')->delete($img);
                    }
                    $v->delete();
                }
            }
            
            // Auto cover logic update
            $coverImage = null;
            $vList = $product->variants()->get();
            $defaultVariant = $vList->where('is_default', 1)->first();
            if ($defaultVariant && !empty($defaultVariant->images)) $coverImage = $defaultVariant->images[0];
            else if ($inStockVariant = $vList->where('stock', '>', 0)->first()) {
                 if (!empty($inStockVariant->images)) $coverImage = $inStockVariant->images[0];
            } else if ($firstVariant = $vList->first()) {
                 if (!empty($firstVariant->images)) $coverImage = $firstVariant->images[0];
            }
            
            if ($coverImage) {
                $currentCover = ProductImage::where('product_id', $product->id)->where('sort_order', -1)->first();
                if ($currentCover) {
                    $currentCover->update(['image_url' => $coverImage]);
                } else {
                    ProductImage::create(['product_id' => $product->id, 'image_url' => $coverImage, 'alt_text' => $product->name, 'sort_order' => -1]);
                }
            }
        }

        return redirect()->route('admin.products.index', ['page' => $request->input('page')])->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        
        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_url);
        }
        
        $product->delete(); // Cascades deletes images and variants records in DB if foreign keys set, but we handle storage files manually or use model events.
        // Migration has onDelete cascade, so records gone. Files need cleanup.

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Products imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
