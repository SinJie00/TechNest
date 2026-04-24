<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductsImport implements OnEachRow, WithHeadingRow
{
    /**
    * @param Row $row
    */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        // Basic Validation
        if (empty($row['name']) || empty($row['price']) || empty($row['category'])) {
            // Skip invalid rows or log error
            return;
        }

        // 1. Find or Create Relations
        // Brand
        $brandId = null;
        if (!empty($row['brand'])) {
            $brand = Brand::firstOrCreate(['name' => $row['brand']], ['slug' => Str::slug($row['brand'])]);
            $brandId = $brand->id;
        }

        // Category
        $category = Category::where('name', $row['category'])->first();
        if (!$category) return; // Skip if category not found (safer than creating)

        // Subcategory
        $subcategoryId = null;
        if (!empty($row['subcategory'])) {
            $subcategory = Subcategory::where('name', $row['subcategory'])
                                    ->where('category_id', $category->id)
                                    ->first();
            if ($subcategory) $subcategoryId = $subcategory->id;
        }

        // 2. Create Product
        $product = Product::create([
            'name'           => $row['name'],
            'description'    => $row['description'] ?? '',
            'price'          => $row['price'],
            'discount_price' => $row['discount_price'] ?? null,
            'stock'          => $row['stock'] ?? 0,
            'brand_id'       => $brandId,
            'category_id'    => $category->id,
            'subcategory_id' => $subcategoryId,
            'status'         => 1, // Default active
            'options'        => $this->parseJson($row['attributes'] ?? null),
        ]);

        // 3. Process Global Images
        if (!empty($row['global_images'])) {
            $images = $this->parseJson($row['global_images']);
            if (is_array($images)) {
                 foreach ($images as $imgUrl) {
                     // In a real bulk import, we'd probably download from URL or map from a zip. 
                     // For this simpler version, we assume URLs or pre-uploaded paths.
                     // If it's a full URL, we might want to download it.
                     // Here we'll just save the string as is if it looks like a path, or placeholder.
                     ProductImage::create([
                         'product_id' => $product->id,
                         'image_path' => $imgUrl,
                         'is_primary' => false 
                     ]);
                 }
            }
        }

        // 4. Generate Variants or Simple Product Logic
        $attributes = $this->parseJson($row['attributes'] ?? null);

        if (!empty($attributes) && is_array($attributes)) {
            // Check if we have valid options
            $validOptions = array_filter($attributes, function($opt) {
                return !empty($opt['name']) && !empty($opt['values']);
            });

            if (count($validOptions) > 0) {
                $this->generateVariants($product, $validOptions, $row);
            }
        }
    }

    private function generateVariants($product, $options, $row)
    {
        // Prepare arrays for Cartesian product
        $optionArrays = [];
        foreach ($options as $opt) {
            $name = $opt['name'];
            $values = $opt['values'];
            
            // Check if values is string "Red,Blue" or array ["Red","Blue"]
            if (is_string($values)) {
                $values = array_map('trim', explode(',', $values));
            }
            
            $optionArrays[] = [
                'name' => $name,
                'values' => $values
            ];
        }

        $combinations = $this->cartesian(array_column($optionArrays, 'values'));

        foreach ($combinations as $combo) {
            $variantAttributes = [];
            foreach ($combo as $index => $value) {
                $variantAttributes[$optionArrays[$index]['name']] = $value;
            }

            // Generate SKU
            $skuSuffix =  implode('-', array_map(function($v) { return strtoupper(substr($v, 0, 3)); }, $combo));
            $sku = strtoupper(substr($product->name, 0, 3)) . '-' . $skuSuffix . '-' . rand(100, 999);

            // Create Variant
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'attributes' => $variantAttributes,
                'price' => $row['price'], // Default to product price
                'discount_price' => $row['discount_price'] ?? null,
                'stock' => floor(($row['stock'] ?? 0) / count($combinations)), // Distribute stock? Or default 0
                'status' => 1
            ]);

            // 5. Variant Images (Optional Mapping)
            // If the row has "variant_images" JSON: {"Color": {"Red": ["img1.jpg"], "Blue": ["img2.jpg"]}}
            if (!empty($row['variant_images'])) {
                $variantImagesMap = $this->parseJson($row['variant_images']);
                // Check if this variant matches any image mapping
                foreach ($variantAttributes as $attrName => $attrValue) {
                    if (isset($variantImagesMap[$attrName][$attrValue])) {
                        $imgs = $variantImagesMap[$attrName][$attrValue];
                        if (is_array($imgs)) {
                            // Update variant's images column (assuming we switched to JSON column or separate table)
                            // Based on previous tasks, ProductVariant has 'images' column (JSON)
                            $variant->images = $imgs;
                            $variant->save();
                        }
                    }
                }
            }
        }
    }

    private function cartesian($input) {
        $result = [[]];
        foreach ($input as $key => $values) {
            $append = [];
            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }
            $result = $append;
        }
        return $result;
    }

    private function parseJson($data) {
        if (empty($data)) return [];
        if (is_array($data)) return $data;
        $decoded = json_decode($data, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }
}
