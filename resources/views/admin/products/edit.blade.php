<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="productForm({{ json_encode($categories) }}, {{ json_encode($product->variants) }}, {{ json_encode($product->options ?? []) }}, {{ json_encode($product->images) }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form id="editProductForm" method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="page" value="{{ request()->query('page') }}">

                        <!-- Top Controls: Status & Variant Toggle -->
                        <div class="flex justify-between items-center mb-6 bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <label for="status" class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" id="status" name="status" value="1" class="sr-only" {{ $product->status ? 'checked' : '' }}>
                                        <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                        <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition {{ $product->status ? 'translate-x-full bg-green-400' : '' }}"></div>
                                    </div>
                                    <div class="ml-3 text-gray-700 font-medium">
                                        Product Active
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <label for="has_variants" class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" id="has_variants" name="has_variants" value="1" class="sr-only" x-model="hasVariants">
                                        <div class="block bg-gray-600 w-14 h-8 rounded-full"></div>
                                        <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition" :class="{'transform translate-x-6': hasVariants}"></div>
                                    </div>
                                    <div class="ml-3 text-gray-700 font-medium">
                                        This product has multiple variants
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                <input type="text" name="name" id="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ old('name', $product->name) }}" required>
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="brand_id" class="block text-sm font-medium text-gray-700">Brand</label>
                                <select name="brand_id" id="brand_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md text-gray-900">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                <select name="category_id" id="category_id" x-model="selectedCategory" @change="updateSubcategories" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md text-gray-900" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id" x-model="selectedSubcategory" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md text-gray-900">
                                    <option value="">Select Subcategory</option>
                                    <template x-for="subcategory in filteredSubcategories" :key="subcategory.id">
                                        <option :value="subcategory.id" x-text="subcategory.name"></option>
                                    </template>
                                </select>
                                @error('subcategory_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Simple Product Fields (Hidden if hasVariants) -->
                        <div x-show="!hasVariants" class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-blue-50 p-4 rounded-lg mb-6">
                            <div class="mb-4">
                                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                                <input type="number" step="0.01" name="price" id="price" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ old('price', $product->price) }}">
                                @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="discount_price" class="block text-sm font-medium text-gray-700">Discount Price ($)</label>
                                <input type="number" step="0.01" name="discount_price" id="discount_price" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ old('discount_price', $product->discount_price) }}">
                                @error('discount_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="stock" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                                <input type="number" name="stock" id="stock" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ old('stock', $product->stock) }}">
                                @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Product Images (Main)</label>
                            <input type="file" name="images[]" multiple accept="image/*" @change="handleFileSelect" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="(image, index) in mainImages" :key="index">
                                    <div class="relative group p-1 border rounded flex flex-col items-center transition-all duration-200 cursor-move"
                                         draggable="true"
                                         @dragstart="dragStart($event, index)"
                                         @dragover.prevent="dragOver($event)"
                                         @drop="drop($event, index)"
                                         @dragend="dragEnd"
                                         :class="{'opacity-50 scale-95': draggingIndex === index, 'ring-2 ring-indigo-500': draggingIndex !== null && draggingIndex !== index}">
                                        
                                        <img :src="image.url" class="h-24 w-24 object-cover rounded pointer-events-none">
                                        
                                        <!-- X Delete Icon -->
                                        <button type="button" @click="removeImage(index)" 
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm hover:bg-red-600 focus:outline-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        
                                        <!-- Drag Handle / Index Indicator -->
                                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-[10px] text-center py-0.5 rounded-b opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                            Drag to sort
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @error('images') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            
                            <!-- Hidden inputs for order and deletions -->
                            <template x-for="(image, index) in mainImages" :key="index">
                                <input type="hidden" name="image_order[]" :value="image.isExisting ? 'existing:' + image.id : 'new:' + index">
                            </template>
                            <template x-for="deletedId in deletedImageIds" :key="deletedId">
                                <input type="hidden" name="deleted_images[]" :value="deletedId">
                            </template>
                        </div>
                        <div x-show="hasVariants" class="mb-4 border-t pt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Product Options & Variants</h3>
                            
                            <!-- Options Configuration -->
                            <div class="mb-6 bg-gray-50 p-4 rounded-md">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">1. Define Options (e.g. Color, Size)</h4>
                                <template x-for="(option, index) in options" :key="index">
                                    <div class="flex flex-col md:flex-row gap-4 mb-2 items-start bg-white p-3 rounded shadow-sm border">
                                        <div class="md:w-1/4">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Option Name</label>
                                            <input type="text" x-model="option.name" placeholder="e.g. Color" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900">
                                            <div class="mt-2">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" x-model="option.requires_image" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-xs text-gray-600">Variants need separate images?</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Option Values (Comma separated)</label>
                                            <input type="text" x-model="option.values" @focus="storeOldValue(option.values); option.error = null" @change="checkChanges(index)" placeholder="e.g. Red, Blue, Green" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" :class="{'border-red-500': option.error}">
                                            <p x-show="option.error" x-text="option.error" class="text-red-500 text-xs mt-1"></p>
                                        </div>
                                        <button type="button" @click="removeOption(index)" class="text-red-500 hover:text-red-700 mt-6">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addOption" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium mt-2">+ Add Another Option</button>
                                
                                <div class="mt-4 border-t pt-4">
                                    <button type="button" @click="generateVariants" class="inline-flex items-center px-4 py-2 bg-indigo-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Regenerate Variants (Merges with existing)
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Grouped Image Uploads -->
                            <div x-show="hasImageRequirement" class="mb-6 bg-blue-50 p-4 rounded-md border border-blue-200">
                                <h4 class="text-sm font-semibold text-blue-800 mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Add/Update Variant Images (Grouped)
                                </h4>
                                <template x-for="(option, optIndex) in options" :key="optIndex">
                                    <div x-show="option.requires_image && option.values" class="mb-4">
                                        <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-2" x-text="option.name + ' Images'"></h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <template x-for="(val, valIndex) in option.values.split(',').map(v => v.trim()).filter(v => v)" :key="valIndex">
                                                <div class="bg-white p-3 rounded shadow-sm border" :class="{'ring-2 ring-indigo-500': defaultGroupName === val}">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <label class="block text-sm font-medium text-gray-900" x-text="val"></label>
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="radio" name="default_cover_group" :value="val" x-model="defaultGroupName" @change="setDefaultVariantFromGroup(option.name, val)" class="text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                            <span class="ml-1 text-xs text-indigo-700 font-semibold tracking-wide">Product Cover</span>
                                                        </label>
                                                    </div>
                                                    
                                                    <input type="file" :name="'option_images[' + option.name + '][' + val + '][]'" multiple accept="image/*" @change="handleGroupedImageSelect($event, option.name, val)" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-2">
                                                    
                                                     <!-- Unified Preview for Existing and New Uploads (Sortable) -->
                                                     <div class="flex flex-wrap gap-2 mt-2" x-show="groupedImages[option.name + '_' + val] && groupedImages[option.name + '_' + val].length">
                                                        <template x-for="(image, index) in groupedImages[option.name + '_' + val]" :key="index">
                                                            <div class="relative group p-1 border rounded flex flex-col items-center transition-all duration-200 cursor-move"
                                                                 draggable="true"
                                                                 @dragstart="groupedDragStart($event, option.name + '_' + val, index)"
                                                                 @dragover.prevent="groupedDragOver($event)"
                                                                 @drop="groupedDrop($event, option.name + '_' + val, index, option.name, val)"
                                                                 :class="{'opacity-50 scale-95': groupedDragging.key === (option.name + '_' + val) && groupedDragging.index === index}">
                                                                 
                                                                <img :src="image.url" class="h-10 w-10 object-cover rounded pointer-events-none">
                                                                
                                                                <!-- Delete Button -->
                                                                <button type="button" @click="removeGroupedImage(option.name + '_' + val, index, option.name, val)" 
                                                                        class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm hover:bg-red-600 focus:outline-none">
                                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                     </div>

                                                     <!-- Order mapping for backend -->
                                                     <template x-for="(image, imgIdx) in groupedImages[option.name + '_' + val]" :key="imgIdx">
                                                        <input type="hidden" :name="'option_image_order[' + option.name + '][' + val + '][]'" :value="image.isExisting ? 'existing:' + image.path : 'new:' + image.fileIndex">
                                                     </template>
                                                 </div>
                                             </template>
                                         </div>
                                     </div>
                                </template>
                                <div class="mt-4">
                                     <template x-for="deletedPath in deletedGroupedPaths" :key="deletedPath">
                                         <input type="hidden" name="deleted_grouped_images[]" :value="deletedPath">
                                     </template>
                                </div>
                            </div>
                            
                            <!-- Generated Variants Table -->
                            <div class="overflow-x-auto" x-show="variants.length > 0">
                                <div class="flex justify-between items-end mb-2">
                                    <h4 class="text-sm font-semibold text-gray-700">2. Manage Variants</h4>
                                    <!-- Bulk Actions -->
                                    <div class="flex gap-2 items-end bg-gray-100 p-2 rounded text-xs shadow-sm">
                                        <div>
                                            <label class="block text-gray-500">Price</label>
                                            <input type="number" step="0.01" x-model="bulk.price" class="w-20 px-1 py-0.5 border rounded text-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500">Discount</label>
                                            <input type="number" step="0.01" x-model="bulk.discount_price" class="w-20 px-1 py-0.5 border rounded text-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500">Stock</label>
                                            <input type="number" x-model="bulk.stock" class="w-16 px-1 py-0.5 border rounded text-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <button type="button" @click="applyBulk" class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition">Apply All</button>
                                    </div>
                                </div>

                                <table class="min-w-full divide-y divide-gray-200 border">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Variant</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price ($)</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Discount ($)</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-3 py-2 relative"><span class="sr-only">Delete</span></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(variant, index) in variants" :key="index">
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                     <input type="hidden" :name="'variants['+index+'][is_default]'" :value="defaultVariantIndex == index ? 1 : 0">
                                                     <span x-text="formatAttributes(variant.attributes)"></span>
                                                     <!-- Hidden input for attributes/id -->
                                                     <input type="hidden" :name="'variants['+index+'][attributes]'" :value="JSON.stringify(variant.attributes)">
                                                     <input type="hidden" :name="'variants['+index+'][id]'" :value="variant.id">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="text" :name="'variants['+index+'][sku]'" x-model="variant.sku" class="block w-32 shadow-sm sm:text-xs border-gray-300 rounded-md text-gray-900">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" step="0.01" :name="'variants['+index+'][price]'" x-model="variant.price" placeholder="Default" class="block w-20 shadow-sm sm:text-xs border-gray-300 rounded-md text-gray-900">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" step="0.01" :name="'variants['+index+'][discount_price]'" x-model="variant.discount_price" placeholder="Default" class="block w-20 shadow-sm sm:text-xs border-gray-300 rounded-md text-gray-900">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="'variants['+index+'][stock]'" x-model="variant.stock" class="block w-16 shadow-sm sm:text-xs border-gray-300 rounded-md text-gray-900">
                                                </td>
                                                 <td class="px-3 py-2 whitespace-nowrap">
                                                    <select :name="'variants['+index+'][status]'" x-model="variant.status" class="block w-20 shadow-sm sm:text-xs border-gray-300 rounded-md text-gray-900">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                    <button type="button" @click="removeVariant(index)" class="text-red-600 hover:text-red-900">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Hidden input for options JSON to save configuration -->
                            <template x-for="(option, index) in options">
                                <div>
                                    <input type="hidden" :name="'options['+index+'][name]'" :value="option.name">
                                    <input type="hidden" :name="'options['+index+'][values]'" :value="option.values">
                                    <input type="hidden" :name="'options['+index+'][requires_image]'" :value="option.requires_image ? 1 : 0">
                                </div>
                            </template>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" required>{{ old('description', $product->description) }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <button type="button" @click="submitForm" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Toggles */
        input:checked ~ .dot {
            transform: translateX(100%);
            background-color: #48bb78;
        }
    </style>

    <script>
        function productForm(categories, existingVariants, existingOptions, existingImages) {
            return {
                categories: categories,
                selectedCategory: {{ old('category_id', $product->category_id) ?? 'null' }},
                selectedSubcategory: {{ old('subcategory_id', $product->subcategory_id) ?? 'null' }},
                filteredSubcategories: (() => {
                    const catId = {{ old('category_id', $product->category_id) ?? 'null' }};
                    if (!catId) return [];
                    const cat = categories.find(c => c.id == catId);
                    return cat ? cat.subcategories : [];
                })(),
                imageUrls: [],
                imageFiles: [],
                existingImageIds: [],
                deletedImageIds: [],
                draggingIndex: null,
                
                // ... variants init ...

                handleFileSelect(event) {
                    const files = event.target.files;
                    if (files) {
                        Array.from(files).forEach(file => {
                            this.imageFiles.push(file);
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.imageUrls.push(e.target.result);
                            };
                            reader.readAsDataURL(file);
                        });
                        this.updateInputFiles();
                    }
                },

                removeImage(index) {
                    // Check if this is an existing image
                    if (index < this.existingImageIds.length) {
                        this.deletedImageIds.push(this.existingImageIds[index]);
                        this.existingImageIds.splice(index, 1);
                    } else {
                        // It's a new image
                        const fileIndex = index - this.existingImageIds.length;
                        this.imageFiles.splice(fileIndex, 1);
                    }
                    this.imageUrls.splice(index, 1);
                    this.updateInputFiles();
                },

                dragStart(event, index) {
                    this.draggingIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                },
                dragOver(event) {
                    event.dataTransfer.dropEffect = 'move';
                },
                dragEnd() {
                    this.draggingIndex = null;
                },
                drop(event, targetIndex) {
                    if (this.draggingIndex === null) return;
                    if (this.draggingIndex === targetIndex) return;

                    const movedUrl = this.imageUrls.splice(this.draggingIndex, 1)[0];
                    this.imageUrls.splice(targetIndex, 0, movedUrl);
                    
                    // Also reorder the tracking arrays
                    const totalExisting = this.existingImageIds.length;
                    if (this.draggingIndex < totalExisting && targetIndex < totalExisting) {
                        // Both existing
                        const movedId = this.existingImageIds.splice(this.draggingIndex, 1)[0];
                        this.existingImageIds.splice(targetIndex, 0, movedId);
                    } else if (this.draggingIndex >= totalExisting && targetIndex >= totalExisting) {
                        // Both new
                        const dragFileIndex = this.draggingIndex - totalExisting;
                        const targetFileIndex = targetIndex - totalExisting;
                        const movedFile = this.imageFiles.splice(dragFileIndex, 1)[0];
                        this.imageFiles.splice(targetFileIndex, 0, movedFile);
                    }
                    
                    this.draggingIndex = null;
                    this.updateInputFiles();
                },

                updateInputFiles() {
                    const dataTransfer = new DataTransfer();
                    this.imageFiles.forEach(file => dataTransfer.items.add(file));
                    document.querySelector('input[name="images[]"]').files = dataTransfer.files;
                },
                
                hasVariants: {{ $hasVariants ? 'true' : 'false' }},
                options: existingOptions && existingOptions.length ? existingOptions.map(o => ({
                    ...o,
                    requires_image: o.requires_image == 1 || o.requires_image == "1" || o.requires_image === true,
                    error: null
                })) : [{ name: '', values: '', requires_image: false, error: null }],
                mainImages: [], // Unified: { id, url, file, isExisting }
                deletedImageIds: [],
                variants: existingVariants.map(v => ({
                    id: v.id,
                    attributes: v.attributes,
                    sku: v.sku,
                    price: v.price,
                    discount_price: v.discount_price,
                    stock: v.stock,
                    status: v.status,
                    is_default: v.is_default,
                    images: v.images || [] // Original paths
                })),
                bulk: { price: '', stock: '', discount_price: '' },
                groupedImages: {}, // Unified: { key: [{ path, url, file, isExisting }] }
                groupedDragging: { key: null, index: null },
                deletedGroupedPaths: [],
                tempValue: '',
                defaultVariantIndex: (() => {
                    if (!existingVariants || existingVariants.length === 0) return null;
                    let idx = existingVariants.findIndex(v => v.is_default == 1 || v.is_default === true);
                    if (idx !== -1) return idx;
                    idx = existingVariants.findIndex(v => v.stock > 0);
                    if (idx !== -1) return idx;
                    return 0;
                })(),
                defaultGroupName: '',
                setDefaultVariantFromGroup(optName, val) {
                    const idx = this.variants.findIndex(v => v.attributes && v.attributes[optName] === val);
                    if (idx !== -1) {
                        this.defaultVariantIndex = idx;
                    }
                },

                get hasImageRequirement() {
                    return this.options.some(o => o.requires_image);
                },

                // Getter to extract existing variant images (used by variants table)
                getVariantImages(v) {
                    // Try to find the grouped images for this variant's attributes
                    for (let [optName, optVal] of Object.entries(v.attributes)) {
                         const key = optName + '_' + optVal;
                         if (this.groupedImages[key]) {
                             return this.groupedImages[key].map(img => img.url);
                         }
                    }
                    return [];
                },

                getOptionImages(optionName, optionValue) {
                    // Find any variant that matches this option value
                    const variant = this.variants.find(v => v.attributes[optionName] === optionValue);
                    return variant ? variant.imagePreviews : [];
                },

                init() {
                    // Initialize Main Images
                    if (existingImages) {
                        existingImages.forEach(img => {
                            this.mainImages.push({
                                id: img.id,
                                url: '/storage/' + img.image_url,
                                isExisting: true,
                                file: null
                            });
                        });
                    }

                    // Initialize Grouped Images (Variants)
                    // Only assign to options that have requires_image: true
                    const requiresImageOptions = this.options.filter(o => o.requires_image).map(o => o.name);
                    
                    this.variants.forEach(v => {
                        if (v.images && v.images.length > 0) {
                            // Find the first attribute that requires images
                            for (const [optName, optVal] of Object.entries(v.attributes)) {
                                if (requiresImageOptions.includes(optName)) {
                                    const key = optName + '_' + optVal;
                                    if (!this.groupedImages[key]) {
                                        this.groupedImages[key] = v.images.map(path => ({
                                            path: path,
                                            url: '/storage/' + path,
                                            isExisting: true,
                                            file: null,
                                            fileIndex: null // Existing images don't have file index
                                        }));
                                    }
                                    break; // Only assign to first matching option
                                }
                            }
                        }
                    });
                    
                    // Force re-selection of subcategory after DOM updates
                    if (this.selectedSubcategory) {
                        const subId = this.selectedSubcategory;
                        this.selectedSubcategory = null;
                        this.$nextTick(() => {
                            this.selectedSubcategory = subId;
                        });
                    }

                    if (this.defaultVariantIndex !== null && this.variants[this.defaultVariantIndex]) {
                        const targetVariant = this.variants[this.defaultVariantIndex];
                        const imgOption = this.options.find(o => o.requires_image);
                        if (imgOption && targetVariant.attributes[imgOption.name]) {
                            this.defaultGroupName = targetVariant.attributes[imgOption.name];
                        }
                    }
                },

                updateSubcategories() {
                    // Use loose comparison (==) or cast to string for ID matching
                    const category = this.categories.find(c => c.id == this.selectedCategory);
                    this.filteredSubcategories = category ? category.subcategories : [];
                },


                // Unified Main Image Handlers
                handleFileSelect(event) {
                    const files = event.target.files;
                    if (files) {
                        Array.from(files).forEach(file => {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.mainImages.push({
                                    id: null,
                                    url: e.target.result,
                                    file: file,
                                    isExisting: false
                                });
                            };
                            reader.readAsDataURL(file);
                        });
                        setTimeout(() => this.updateInputFiles(), 100);
                    }
                },
                removeImage(index) {
                    const img = this.mainImages[index];
                    if (img.isExisting) {
                        this.deletedImageIds.push(img.id);
                    }
                    this.mainImages.splice(index, 1);
                    this.updateInputFiles();
                },
                dragStart(event, index) {
                    this.draggingIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                },
                dragOver(event) {
                    event.dataTransfer.dropEffect = 'move';
                },
                drop(event, targetIndex) {
                    if (this.draggingIndex === null) return;
                    const movedItem = this.mainImages.splice(this.draggingIndex, 1)[0];
                    this.mainImages.splice(targetIndex, 0, movedItem);
                    this.draggingIndex = null;
                    this.updateInputFiles();
                },
                dragEnd() {
                    this.draggingIndex = null;
                },
                updateInputFiles() {
                    const dataTransfer = new DataTransfer();
                    this.mainImages.filter(img => !img.isExisting).forEach(img => {
                        if (img.file) dataTransfer.items.add(img.file);
                    });
                    const input = document.querySelector('input[name="images[]"]');
                    if (input) input.files = dataTransfer.files;
                },
                // Unified Grouped (Variant) Image Handlers
                handleGroupedImageSelect(event, optName, val) {
                    const key = optName + '_' + val;
                    const files = event.target.files;
                    if (!this.groupedImages[key]) this.groupedImages[key] = [];

                    if (files) {
                        // Calculate the current count of new files for this group
                        const currentNewCount = this.groupedImages[key].filter(img => !img.isExisting).length;
                        
                        Array.from(files).forEach((file, idx) => {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.groupedImages[key].push({
                                    path: null,
                                    url: e.target.result,
                                    file: file,
                                    isExisting: false,
                                    fileIndex: currentNewCount + idx // Track original file input index
                                });
                            };
                            reader.readAsDataURL(file);
                        });
                        setTimeout(() => this.updateGroupedInput(key, optName, val), 100);
                    }
                },
                removeGroupedImage(key, index, optName, val) {
                    const img = this.groupedImages[key][index];
                    if (img.isExisting) {
                        this.deletedGroupedPaths.push(img.path);
                    }
                    this.groupedImages[key].splice(index, 1);
                    
                    // Recalculate fileIndex for remaining new images
                    let newIdx = 0;
                    this.groupedImages[key].forEach(img => {
                        if (!img.isExisting) {
                            img.fileIndex = newIdx++;
                        }
                    });
                    
                    this.updateGroupedInput(key, optName, val);
                },
                groupedDragStart(event, key, index) {
                    this.groupedDragging = { key: key, index: index };
                    event.dataTransfer.effectAllowed = 'move';
                },
                groupedDragOver(event) {
                    event.dataTransfer.dropEffect = 'move';
                },
                groupedDrop(event, key, targetIndex, optName, val) {
                    const dragData = this.groupedDragging;
                    if (!dragData.key || dragData.key !== key) return;
                    const movedItem = this.groupedImages[key].splice(dragData.index, 1)[0];
                    this.groupedImages[key].splice(targetIndex, 0, movedItem);
                    this.groupedDragging = { key: null, index: null };
                    // Note: Don't recalculate fileIndex on drag - the order mapping handles this
                    this.updateGroupedInput(key, optName, val);
                },
                updateGroupedInput(key, optName, val) {
                    const inputName = `option_images[${optName}][${val}][]`;
                    const input = document.querySelector(`input[name="${CSS.escape(inputName)}"]`) || document.getElementsByName(inputName)[0];
                    if (input) {
                         const dataTransfer = new DataTransfer();
                         // Add files in their current display order
                         this.groupedImages[key].filter(img => !img.isExisting && img.file).forEach(img => {
                             dataTransfer.items.add(img.file);
                         });
                         input.files = dataTransfer.files;
                         
                         // Reassign fileIndex based on new file order in DataTransfer
                         let idx = 0;
                         this.groupedImages[key].forEach(img => {
                             if (!img.isExisting && img.file) {
                                 img.fileIndex = idx++;
                             }
                         });
                    }
                },

                // Options Management
                addOption() {
                    this.options.push({ name: '', values: '', requires_image: false, error: null });
                },
                removeOption(index) {
                    this.options.splice(index, 1);
                },
                
                storeOldValue(val) {
                    this.tempValue = val;
                },

                checkChanges(index) {
                    if (this.variants.length > 0 && this.options[index].values !== this.tempValue) {
                        Swal.fire({
                            title: 'New Variant Detected',
                            text: 'You changed the variants. Do you want to regenerate variants?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'OK (Generate)',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.generateVariants();
                            } else {
                                this.options[index].values = this.tempValue;
                            }
                        });
                    }
                },

                // Variant Generation
                generateVariants() {
                    // Reset errors
                    let hasError = false;
                    this.options.forEach(o => o.error = null);

                    this.options.forEach(option => {
                        if (option.name && !option.values) {
                            option.error = 'Please enter option values (comma separated)';
                            hasError = true;
                        } else if (!option.name && option.values) {
                            option.error = 'Please enter an option name';
                            hasError = true;
                        }
                    });

                    if (hasError) return;

                    const validOptions = this.options.filter(o => o.name && o.values);
                    if (validOptions.length === 0) return;

                    const optionArrays = validOptions.map(o => ({
                        name: o.name,
                        values: o.values.split(',').map(v => v.trim()).filter(v => v)
                    }));
                    
                    const combinations = this.cartesian(optionArrays.map(o => o.values));
                    
                    // Smart merge
                    const newVariants = combinations.map(combo => {
                        const attributes = {};
                        combo.forEach((value, index) => {
                            attributes[optionArrays[index].name] = value;
                        });

                        // Check if variant exists
                        const existing = this.variants.find(v => JSON.stringify(v.attributes) === JSON.stringify(attributes));
                        if (existing) return existing;

                        const productName = document.getElementById('name').value || 'PROD';
                        const skuSuffix = combo.map(v => v.toUpperCase().substr(0, 3)).join('-');
                        const sku = `${productName.toUpperCase().substr(0, 3)}-${skuSuffix}-${Math.floor(Math.random() * 1000)}`;

                        return {
                            attributes: attributes,
                            sku: sku,
                            price: document.getElementById('price').value || '',
                            discount_price: document.getElementById('discount_price').value || '',
                            stock: document.getElementById('stock').value || 0,
                            status: 1,
                            imagePreviews: []
                        };
                    });

                    this.variants = newVariants;
                    if (this.defaultVariantIndex === null && this.variants.length > 0) {
                        this.defaultVariantIndex = 0;
                    }

                    if (this.defaultVariantIndex !== null && this.variants[this.defaultVariantIndex]) {
                        const imgOption = this.options.find(o => o.requires_image);
                        if (imgOption && this.variants[this.defaultVariantIndex].attributes[imgOption.name]) {
                            this.defaultGroupName = this.variants[this.defaultVariantIndex].attributes[imgOption.name];
                        }
                    }
                },

                applyBulk() {
                    if (this.bulk.price !== '') {
                        this.variants.forEach(v => v.price = this.bulk.price);
                    }
                    if (this.bulk.discount_price !== '') {
                        this.variants.forEach(v => v.discount_price = this.bulk.discount_price);
                    }
                    if (this.bulk.stock !== '') {
                        this.variants.forEach(v => v.stock = this.bulk.stock);
                    }
                },

                cartesian(args) {
                    var r = [], max = args.length-1;
                    function helper(arr, i) {
                        for (var j=0, l=args[i].length; j<l; j++) {
                            var a = arr.slice(0); 
                            a.push(args[i][j]);
                            if (i==max) r.push(a);
                            else helper(a, i+1);
                        }
                    }
                    helper([], 0);
                    return r;
                },

                handleVariantImage(event, index) {
                    const files = event.target.files;
                    if (files) {
                        this.variants[index].imagePreviews = []; // Keep for visual ref in table if needed
                        for (let i = 0; i < files.length; i++) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.variants[index].imagePreviews.push(e.target.result);
                            };
                            reader.readAsDataURL(files[i]);
                        }
                    }
                },

                handleVariantImage(event, index) {
                    const files = event.target.files;
                    this.variants[index].imagePreviews = [];
                    if (files) {
                        for (let i = 0; i < files.length; i++) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.variants[index].imagePreviews.push(e.target.result);
                            };
                            reader.readAsDataURL(files[i]);
                        }
                    }
                },

                removeVariant(index) {
                    this.variants.splice(index, 1);
                },

                formatAttributes(attributes) {
                    return Object.entries(attributes).map(([key, value]) => `${key}: ${value}`).join(', ');
                },

                submitForm() {
                    // Sync CKEditor data to textarea
                    if (window.editor) {
                        document.querySelector('#description').value = window.editor.getData();
                    }

                    const form = document.getElementById('editProductForm');
                     if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    // Client-side check for variant images
                    if (this.hasVariants && this.hasImageRequirement) {
                        for (let option of this.options) {
                            if (option.requires_image && option.values) {
                                const values = option.values.split(',').map(v => v.trim()).filter(v => v);
                                 for (let val of values) {
                                    const key = option.name + '_' + val;
                                    const variantImages = this.groupedImages[key] || [];
                                    
                                    if (variantImages.length === 0) {
                                         Swal.fire({
                                            icon: 'error',
                                            title: 'Missing Images',
                                            text: `Please upload images for option: ${option.name} - ${val}`,
                                            confirmButtonColor: '#d33'
                                        });
                                        return;
                                    }
                                }
                            }
                        }
                    }

                    if (this.hasVariants && this.variants.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Variants Required',
                            text: 'This product has variants enabled but none generated. Please add variants or disable the option.',
                            confirmButtonColor: '#3085d6',
                        });
                        return;
                    }
                    
                    form.submit();
                }
            }
        }
    </script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable {
            color: #000 !important;
            background-color: white !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            ClassicEditor
                .create(document.querySelector('#description'))
                .then(editor => {
                    window.editor = editor;
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
</x-admin-layout>
