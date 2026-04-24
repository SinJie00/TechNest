<x-app-layout>
    <div class="bg-white" x-data="product({{ $id }})" x-init="init()">
        <div class="max-w-2xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:max-w-7xl lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
                <!-- Image gallery -->
                <div class="flex flex-col-reverse">
                    <div class="mt-6 w-full max-w-2xl mx-auto sm:block lg:max-w-none">
                        <div class="grid grid-cols-4 gap-6" aria-orientation="horizontal" role="tablist">
                            <template x-for="(image, index) in displayImages" :key="index">
                                <button @click="activeImage = image.image_url"
                                    class="relative h-24 bg-white rounded-md flex items-center justify-center text-sm font-medium uppercase text-gray-900 cursor-pointer hover:bg-gray-50 focus:outline-none focus:ring focus:ring-offset-4 focus:ring-opacity-50"
                                    :class="activeImage === image.image_url ? 'ring ring-indigo-500' : ''"
                                    aria-controls="tabs-1-panel-1" role="tab" type="button">
                                    <span class="sr-only">Image view</span>
                                    <span class="absolute inset-0 rounded-md overflow-hidden">
                                        <img :src="getImageUrl(image.image_url)" alt=""
                                            class="w-full h-full object-center object-cover">
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="w-full aspect-w-1 aspect-h-1">
                        <!-- Main Image -->
                        <img :src="getImageUrl(activeImage)" alt=""
                            class="w-full h-full object-center object-cover sm:rounded-lg">
                    </div>
                </div>

                <!-- Product info -->
                <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900" x-text="product.name"></h1>

                    <!-- Price Display -->
                    <div class="mt-3">
                        <h2 class="sr-only">Product information</h2>

                        <!-- Variant Product - No selection yet -->
                        <template x-if="hasVariants && !selectedVariant">
                            <div>
                                <p class="text-3xl text-gray-900">
                                    <span x-text="priceDisplay"></span>
                                </p>
                            </div>
                        </template>

                        <!-- Variant Product - Selection made -->
                        <template x-if="hasVariants && selectedVariant">
                            <div>
                                <p class="text-3xl text-gray-900" x-show="!selectedVariant.discount_price"
                                    x-text="'RM ' + parseFloat(selectedVariant.price).toFixed(2)"></p>
                                <p class="text-3xl text-red-600" x-show="selectedVariant.discount_price">
                                    <span x-text="'RM ' + parseFloat(selectedVariant.discount_price).toFixed(2)"></span>
                                    <span class="text-base text-gray-500 line-through ml-2"
                                        x-text="'RM ' + parseFloat(selectedVariant.price).toFixed(2)"></span>
                                </p>
                            </div>
                        </template>

                        <!-- Simple Product -->
                        <template x-if="!hasVariants">
                            <div>
                                <p class="text-3xl text-gray-900" x-show="!product.discount_price"
                                    x-text="'RM ' + parseFloat(product.price || 0).toFixed(2)"></p>
                                <p class="text-3xl text-red-600" x-show="product.discount_price">
                                    <span x-text="'RM ' + parseFloat(product.discount_price).toFixed(2)"></span>
                                    <span class="text-base text-gray-500 line-through ml-2"
                                        x-text="'RM ' + parseFloat(product.price || 0).toFixed(2)"></span>
                                </p>
                            </div>
                        </template>
                    </div>

                    <!-- Stock Display -->
                    <div class="mt-2">
                        <template x-if="hasVariants && !selectedVariant">
                            <p class="text-sm font-medium" :class="totalStock > 0 ? 'text-green-600' : 'text-red-600'" x-text="totalStock > 0 ? 'In Stock' : 'Out of Stock'"></p>
                        </template>
                        <template x-if="hasVariants && selectedVariant">
                            <div>
                                <p x-show="selectedVariant.stock > 0" class="text-sm font-bold text-orange-600" x-text="'Only ' + selectedVariant.stock + ' units left!'"></p>
                                <p x-show="selectedVariant.stock <= 0" class="text-sm font-medium text-red-600">Out of Stock</p>
                            </div>
                        </template>
                        <template x-if="!hasVariants">
                            <div>
                                <p x-show="product.stock > 0" class="text-sm font-bold text-orange-600" x-text="'Only ' + product.stock + ' units left!'"></p>
                                <p x-show="product.stock <= 0" class="text-sm font-medium text-red-600">Out of Stock</p>
                            </div>
                        </template>
                    </div>



                    <form class="mt-6" @submit.prevent="addToCart()">
                        <!-- Option Selectors for Variant Products -->
                        <div x-show="hasVariants && productOptions.length > 0" class="space-y-4">
                            <template x-for="option in productOptions" :key="option.name">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900" x-text="option.name"></h3>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="value in option.values" :key="value">
                                            <button type="button" @click="selectOption(option.name, value)" :class="selectedOptions[option.name] === value 
                                                        ? 'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-500' 
                                                        : 'border-gray-300 hover:bg-gray-50'"
                                                class="relative border rounded-md py-2 px-4 flex items-center justify-center text-sm font-medium uppercase text-gray-900 cursor-pointer focus:outline-none transition-all">
                                                <span x-text="value"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="mt-6">
                            <label class="text-sm font-medium text-gray-900">Quantity</label>
                            <div class="mt-2 flex items-center space-x-3">
                                <button type="button" @click="decreaseQuantity()"
                                    class="w-10 h-10 rounded-md border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <input type="number" x-model.number="quantity" min="1" :max="maxQuantity"
                                    class="w-16 text-center border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="button" @click="increaseQuantity()"
                                    class="w-10 h-10 rounded-md border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <div class="mt-6 flex sm:flex-col1">
                            <button type="submit" :disabled="!canAddToCart"
                                :class="canAddToCart ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed'"
                                class="max-w-xs flex-1 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500 sm:w-full">
                                <span x-show="(!hasVariants || selectedVariant) && (selectedVariant ? selectedVariant.stock : product.stock) > 0">Add to Cart</span>
                                <span x-show="(!hasVariants || selectedVariant) && (selectedVariant ? selectedVariant.stock : product.stock) <= 0">Out of Stock</span>
                                <span x-show="hasVariants && !selectedVariant">Select Options</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Full-Width Description Section -->
            <div class="mt-16 border-t border-gray-200 pt-10">
                <h3 class="text-lg font-medium text-gray-900">Product Description</h3>
                <div class="mt-6 text-base text-gray-700 space-y-6 wysiwyg-content max-w-none"
                    x-html="product.description">
                </div>
            </div>


        </div>
    </div>

    <script>
        function product(id) {
            return {
                product: {},
                activeImage: null,
                selectedVariant: null,
                selectedOptions: {},
                quantity: 1,
                averageRating: 0,
                displayImages: [],
                mainProductImages: [], // Store main images separately

                get hasVariants() {
                    return this.product.variants && this.product.variants.length > 0;
                },

                get productOptions() {
                    if (!this.product.options) return [];
                    return this.product.options.map(opt => ({
                        name: opt.name,
                        values: opt.values.split(',').map(v => v.trim()).filter(v => v)
                    }));
                },

                get totalStock() {
                    if (!this.hasVariants) return this.product.stock || 0;
                    return this.product.variants.reduce((sum, v) => sum + (v.stock || 0), 0);
                },

                get priceDisplay() {
                    if (!this.hasVariants) {
                        return 'RM ' + parseFloat(this.product.price || 0).toFixed(2);
                    }

                    const prices = this.product.variants.map(v =>
                        parseFloat(v.discount_price || v.price || 0)
                    );
                    const minPrice = Math.min(...prices);
                    const maxPrice = Math.max(...prices);

                    if (minPrice === maxPrice) {
                        return 'RM ' + minPrice.toFixed(2);
                    }
                    return 'From RM ' + minPrice.toFixed(2);
                },

                get maxQuantity() {
                    if (this.selectedVariant) return this.selectedVariant.stock || 1;
                    if (!this.hasVariants) return this.product.stock || 1;
                    return 99;
                },

                get canAddToCart() {
                    if (this.hasVariants && !this.selectedVariant) return false;
                    const stock = this.selectedVariant ? this.selectedVariant.stock : this.product.stock;
                    return stock > 0 && this.quantity > 0;
                },

                init() {
                    this.$watch('quantity', val => {
                        if (val === '') return;
                        const parsed = parseInt(val);
                        if(isNaN(parsed) || parsed < 1) this.quantity = 1;
                        else if(parsed > this.maxQuantity) this.quantity = this.maxQuantity;
                    });
                    this.$watch('selectedVariant', () => {
                        if (this.quantity > this.maxQuantity) this.quantity = Math.max(1, this.maxQuantity);
                    });

                    fetch('/api/products/' + id)
                        .then(res => res.json())
                        .then(data => {
                            this.product = data;
                            this.mainProductImages = (this.product.images || []).filter(img => img.sort_order >= 0);
                            this.displayImages = this.mainProductImages;

                            if (this.product.reviews && this.product.reviews.length) {
                                let sum = this.product.reviews.reduce((a, b) => a + b.rating, 0);
                                this.averageRating = Math.round(sum / this.product.reviews.length);
                            } else {
                                this.averageRating = 0; // Ensure 0 if no reviews
                            }

                            // Set initial image
                            if (this.hasVariants) {
                                // Find default variant
                                let defaultVariant = this.product.variants.find(v => v.is_default);
                                if (!defaultVariant) defaultVariant = this.product.variants.find(v => v.stock > 0);
                                if (!defaultVariant && this.product.variants.length > 0) defaultVariant = this.product.variants[0];

                                if (defaultVariant && defaultVariant.images && defaultVariant.images.length > 0) {
                                    this.displayImages = [
                                        ...defaultVariant.images.map(img => ({ image_url: img })),
                                        ...this.mainProductImages
                                    ];
                                } else {
                                    this.displayImages = this.mainProductImages;
                                }
                            }

                            if (this.displayImages.length) {
                                this.activeImage = this.displayImages[0].image_url;
                            }

                            // Don't auto-select options - let user select them
                            // Only initialize the selectedOptions object
                            if (this.hasVariants && this.product.options) {
                                this.product.options.forEach(opt => {
                                    this.selectedOptions[opt.name] = null;
                                });
                            }
                        });
                },

                selectOption(optionName, value) {
                    this.selectedOptions[optionName] = value;
                    this.findMatchingVariant();
                },

                findMatchingVariant() {
                    if (!this.hasVariants) return;

                    // 1. Immediately update gallery if the image-driving option is changed
                    if (this.product.options) {
                        const imgOption = this.product.options.find(o => o.requires_image == 1 || o.requires_image === true || o.requires_image === '1');
                        if (imgOption && this.selectedOptions[imgOption.name]) {
                            const selectedImgVal = this.selectedOptions[imgOption.name];
                            const variantWithImage = this.product.variants.find(v => v.attributes && v.attributes[imgOption.name] === selectedImgVal);
                            
                            if (variantWithImage && variantWithImage.images && variantWithImage.images.length > 0) {
                                // Only update arrays and active image if the selection actually targets a new image grouping
                                const firstNew = variantWithImage.images[0];
                                const firstCurrent = this.displayImages.length ? this.displayImages[0].image_url : null;
                                if (firstCurrent !== firstNew && firstCurrent !== ('/storage/' + firstNew)) {
                                    this.displayImages = [
                                        ...variantWithImage.images.map(img => ({ image_url: img })),
                                        ...this.mainProductImages
                                    ];
                                    this.activeImage = this.displayImages[0].image_url;
                                }
                            } else if (variantWithImage) {
                                // Fallback to main images if this specific option value has no grouped images
                                this.displayImages = this.mainProductImages;
                                if (this.displayImages.length) {
                                    this.activeImage = this.displayImages[0].image_url;
                                }
                            }
                        }
                    }

                    // 2. Check if all options are selected for actual Cart processing
                    const allSelected = this.productOptions.every(opt =>
                        this.selectedOptions[opt.name] !== null && this.selectedOptions[opt.name] !== undefined
                    );

                    if (!allSelected) {
                        this.selectedVariant = null;
                        return;
                    }

                    // Find variant that matches all selected options exactly
                    this.selectedVariant = this.product.variants.find(v => {
                        return Object.entries(this.selectedOptions).every(([key, val]) => {
                            return v.attributes && v.attributes[key] === val;
                        });
                    }) || null;

                    // Adjust quantity if needed
                    if (this.selectedVariant && this.quantity > this.selectedVariant.stock) {
                        this.quantity = Math.max(1, this.selectedVariant.stock);
                    }
                },

                getImageUrl(path) {
                    if (!path) return 'https://via.placeholder.com/600';
                    if (path.startsWith('http')) return path;
                    return '/storage/' + path;
                },

                increaseQuantity() {
                    if (this.quantity < this.maxQuantity) {
                        this.quantity++;
                    }
                },

                decreaseQuantity() {
                    if (this.quantity > 1) {
                        this.quantity--;
                    }
                },

                addToCart() {
                    if (!this.canAddToCart) {
                        return;
                    }

                    fetch('/api/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: this.product.id,
                            variant_id: this.selectedVariant ? this.selectedVariant.id : null,
                            quantity: this.quantity
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            window.dispatchEvent(new CustomEvent('cart-updated'));
                            window.dispatchEvent(new CustomEvent('cart-open'));
                        });
                }
            }
        }
    </script>
</x-app-layout>