<x-app-layout>
    <div class="bg-white" x-data="shop()" x-init="init()">
        <div>
            <!-- Mobile filter dialog -->
            <!-- (Simplified: skipped mobile dialog for brevity, using standard sidebar) -->

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative z-10 flex flex-col pt-6">
                    <!-- Breadcrumbs -->
                    <nav class="flex mb-4 text-sm text-gray-500" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            <li><a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a></li>
                            <li x-show="filters.category_id">
                                <span class="mx-2">&rarr;</span>
                                <a href="#" @click.prevent="clearSubcat(); fetchProducts()" class="hover:text-indigo-600" x-text="currentCategoryName"></a>
                            </li>
                            <li x-show="filters.subcategory_id">
                                <span class="mx-2">&rarr;</span>
                                <span class="text-gray-900 font-medium" x-text="currentSubcategoryName"></span>
                            </li>
                        </ol>
                    </nav>

                    <div class="flex items-baseline justify-between border-b border-gray-200 pb-6 pt-8">
                        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900" x-text="pageTitle"></h1>

                        <div class="flex items-center space-x-6">
                            <button x-show="isSubFiltered" @click="resetListingFilters()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest flex items-center transition-all">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Clear Filters
                            </button>
                            <!-- Sort options -->
                            <div class="relative inline-block text-left" x-data="{ sortOpen: false }" @click.away="sortOpen = false">
                                <button @click="sortOpen = !sortOpen" class="group inline-flex justify-center text-sm font-medium text-gray-700 hover:text-gray-900">
                                    Sort
                                    <svg class="flex-shrink-0 -mr-1 ml-1 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="sortOpen" style="display: none;" class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-10">
                                    <div class="py-1">
                                        <button @click="filters.sort = 'newest'; fetchProducts(); sortOpen = false" class="block px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 w-full text-left" :class="filters.sort === 'newest' ? 'font-bold text-indigo-600' : ''">Newest</button>
                                        <button @click="filters.sort = 'price_asc'; fetchProducts(); sortOpen = false" class="block px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 w-full text-left" :class="filters.sort === 'price_asc' ? 'font-bold text-indigo-600' : ''">Price: Low to High</button>
                                        <button @click="filters.sort = 'price_desc'; fetchProducts(); sortOpen = false" class="block px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 w-full text-left" :class="filters.sort === 'price_desc' ? 'font-bold text-indigo-600' : ''">Price: High to Low</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <section aria-labelledby="products-heading" class="pt-6 pb-24">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-x-8 gap-y-10">
                        <!-- Filters -->
                        <form class="hidden lg:block space-y-8">
                            <!-- Price Range -->
                            <div class="border-b border-gray-200 py-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">Price Range</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-xs font-bold">RM</span>
                                            <input type="number" x-model="filters.price_min" placeholder="Min" class="w-full border-gray-300 rounded text-sm pl-9 p-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <span class="text-gray-400 font-bold">-</span>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-xs font-bold">RM</span>
                                            <input type="number" x-model="filters.price_max" placeholder="Max" class="w-full border-gray-300 rounded text-sm pl-9 p-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    <button type="button" @click="fetchProducts()" class="w-full bg-indigo-600 text-white text-xs font-bold py-2.5 rounded shadow-sm hover:bg-indigo-700 transition duration-200">APPLY PRICE</button>
                                </div>
                            </div>
                            
                            <!-- Dynamic Storage Filter -->
                            <div class="border-b border-gray-200 py-6" x-show="facets.sizes.length > 0">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">Storage Capacity</h3>
                                <div class="space-y-2">
                                    <template x-for="size in facets.sizes" :key="size">
                                        <label class="flex items-center text-sm text-gray-600 cursor-pointer hover:text-indigo-600 group">
                                            <input type="checkbox" :value="size" x-model="filters.storage" @change="fetchProducts()" class="h-4 w-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500 mr-2 group-hover:border-indigo-500">
                                            <span x-text="size"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- Dynamic Color Filter -->
                            <div class="py-6" x-show="facets.colors.length > 0">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">Colors</h3>
                                <div class="flex flex-wrap gap-3">
                                    <template x-for="color in facets.colors" :key="color">
                                        <button type="button" 
                                            @click="toggleColor(color); fetchProducts()"
                                            class="w-10 h-10 rounded-full transition-all duration-200 relative flex items-center justify-center group focus:outline-none border border-gray-200 hover:scale-110" 
                                            :class="filters.colors.includes(color) ? 'ring-2 ring-offset-2 ring-indigo-500' : 'hover:ring-2 hover:ring-offset-2 hover:ring-indigo-400'"
                                            :title="color" 
                                            :style="'background-color: ' + getColorStyle(color)">
                                            <!-- Checkmark (Only when selected) -->
                                            <div x-show="filters.colors.includes(color)" class="flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </form>

                        <!-- Product grid -->
                        <div class="lg:col-span-3">
                            <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                                <template x-for="product in products" :key="product.id">
                                     <div class="group relative bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 flex flex-col">
                                        <div class="relative w-full aspect-square bg-gray-200 rounded-md overflow-hidden group-hover:opacity-75">
                                            <a :href="'/product/' + product.id">
                                                <img :src="getCoverImage(product)" alt="" class="w-full h-full object-center object-cover">
                                            </a>
                                            <!-- Out of Stock Overlay -->
                                            <template x-if="product.stock <= 0">
                                                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                                                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Out of Stock</span>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-4 flex flex-col flex-1">
                                            <div class="flex-1">
                                                <p class="text-xs text-indigo-600 font-bold uppercase tracking-widest mb-1" x-text="product.brand ? product.brand.name : ''"></p>
                                                <h3 class="text-base font-bold text-gray-900 leading-tight">
                                                    <a :href="'/product/' + product.id" class="hover:underline">
                                                        <span x-text="product.name"></span>
                                                    </a>
                                                </h3>
                                            </div>
                                            
                                            <div class="mt-3 flex items-center justify-between">
                                                <div x-html="getPriceHtml(product)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <div x-show="products.length === 0" class="col-span-full text-center py-12 text-gray-500">
                                    No products found matching your criteria.
                                </div>
                            </div>
                            
                            <!-- Pagination Controls (Simplified) -->
                            <div class="mt-8 flex justify-between" x-show="pagination.total > pagination.per_page">
                                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-4 py-2 border rounded disabled:opacity-50">Previous</button>
                                <span class="text-sm text-gray-700 self-center" x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                                <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-4 py-2 border rounded disabled:opacity-50">Next</button>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
    
    <script>
        function shop() {
            return {
                products: [],
                categories: [],
                filters: {
                    category_id: null,
                    subcategory_id: null,
                    price_min: null,
                    price_max: null,
                    brand: '',
                    brand_id: null,
                    sort: 'newest',
                    search: '',
                    storage: [],
                    colors: [],
                    rating: null
                },
                facets: {
                    sizes: [],
                    colors: []
                },
                get isFiltered() {
                    return this.filters.category_id || this.filters.subcategory_id || this.filters.price_min || this.filters.price_max || this.filters.storage.length > 0 || this.filters.colors.length > 0 || this.filters.search;
                },
                get isSubFiltered() {
                    return this.filters.price_min || this.filters.price_max || this.filters.storage.length > 0 || this.filters.colors.length > 0 || this.filters.search;
                },
                get pageTitle() {
                    if (this.filters.search) return `Search results for "${this.filters.search}"`;
                    if (this.filters.subcategory_id) return this.currentSubcategoryName;
                    if (this.filters.category_id) return this.currentCategoryName;
                    return 'Our Collection';
                },
                get currentCategoryName() {
                    const cat = this.categories.find(c => c.id == this.filters.category_id);
                    return cat ? cat.name : 'Category';
                },
                get currentSubcategoryName() {
                    if(!this.filters.subcategory_id) return 'Subcategory';
                    let matchedSub = null;
                    this.categories.forEach(cat => {
                        const sub = cat.children.find(s => s.id == this.filters.subcategory_id);
                        if (sub) matchedSub = sub;
                    });
                    return matchedSub ? matchedSub.name : 'Subcategory';
                },
                getColorStyle(color) {
                    const map = {
                        'Mist Blue': '#B0C4DE',
                        'Sage Green': '#8A9A5B',
                        'Lavender': '#E6E6FA',
                        'Rose Gold': '#B76E79',
                        'Space Grey': '#53565A',
                        'Midnight': '#191970',
                        'Starlight': '#F2F2E1'
                    };
                    return map[color] || color.toLowerCase();
                },
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 12,
                    total: 0
                },
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('category_id')) this.filters.category_id = urlParams.get('category_id');
                    if (urlParams.has('subcategory_id')) this.filters.subcategory_id = urlParams.get('subcategory_id');
                    if (urlParams.has('brand_id')) this.filters.brand_id = urlParams.get('brand_id');
                    if (urlParams.has('search')) this.filters.search = urlParams.get('search');
                    if (urlParams.has('sort')) this.filters.sort = urlParams.get('sort');

                    this.fetchCategories();
                    this.fetchProducts();
                },
                clearSubcat() {
                    this.filters.subcategory_id = null;
                },
                toggleColor(color) {
                    if (this.filters.colors.includes(color)) {
                        this.filters.colors = this.filters.colors.filter(c => c !== color);
                    } else {
                        this.filters.colors.push(color);
                    }
                },
                fetchCategories() {
                    fetch('/api/categories')
                        .then(res => res.json())
                        .then(data => {
                            this.categories = data;
                        });
                },
                fetchProducts(page = 1) {
                    let params = new URLSearchParams();
                    // Only add occupied filters to the query string to prevent sending "null" strings to backend
                    Object.keys(this.filters).forEach(key => {
                        const val = this.filters[key];
                        if (val !== null && val !== undefined && val !== '') {
                            if (Array.isArray(val)) {
                                if (val.length > 0) {
                                    // Laravel handles array-like strings better if formatted correctly or handled in backend
                                    params.append(key, val.join(','));
                                }
                            } else {
                                params.append(key, val);
                            }
                        }
                    });
                    params.append('page', page);
                    
                    fetch(`/api/products?${params.toString()}`)
                        .then(res => res.json())
                        .then(data => {
                            // If data is from Laravels pagination, it will be in data.data or the root depending on transform
                            this.products = data.data || [];
                            if (data.facets) this.facets = data.facets;
                            this.pagination = {
                                current_page: data.current_page,
                                last_page: data.last_page,
                                per_page: data.per_page,
                                total: data.total
                            };
                        });
                },
                clearAllFilters() {
                    this.filters = {
                        category_id: null,
                        subcategory_id: null,
                        price_min: null,
                        price_max: null,
                        brand: '',
                        brand_id: null,
                        sort: 'newest',
                        search: '',
                        storage: [],
                        colors: [],
                        rating: null
                    };
                    this.fetchProducts();
                    window.history.pushState({}, '', window.location.pathname);
                },
                resetListingFilters() {
                    // Reset only product-level filters, preserving Category/Subcategory
                    this.filters.price_min = null;
                    this.filters.price_max = null;
                    this.filters.storage = [];
                    this.filters.colors = [];
                    this.filters.search = '';
                    this.fetchProducts();
                },
                changePage(page) {
                    if(page >= 1 && page <= this.pagination.last_page) {
                        this.fetchProducts(page);
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    }
                },
                getPriceHtml(product) {
                    if (!product.variants || product.variants.length === 0) {
                        if (product.discount_price) {
                             return `<span class="text-sm font-bold text-red-600">RM ${parseFloat(product.discount_price).toFixed(2)}</span> <span class="text-sm text-gray-400 line-through">RM ${parseFloat(product.price).toFixed(2)}</span>`;
                        }
                        return `RM ${parseFloat(product.price || 0).toFixed(2)}`;
                    }
                    const prices = product.variants.map(v => parseFloat(v.discount_price || v.price || 0));
                    const minPrice = Math.min(...prices);
                    const maxPrice = Math.max(...prices);
                    if (minPrice === maxPrice) {
                        return `<span class="text-lg font-bold text-gray-900">RM ${minPrice.toFixed(2)}</span>`;
                    }
                    return `<span class="text-lg font-bold text-gray-900">From RM ${minPrice.toFixed(2)}</span>`;
                },
                getCoverImage(product) {
                    if (!product.images || !product.images.length) return 'https://via.placeholder.com/300';
                    // We already sorted images by sort_order in the model, so [0] is the cover
                    const path = product.images[0].image_url;
                    if (path.startsWith('http')) return path;
                    return '/storage/' + path;
                },
                quickAddToCart(product) {
                    if (product.variants && product.variants.length > 0) {
                        // Redirect to product page for selection if variants exist
                        window.location.href = '/product/' + product.id;
                        return;
                    }
                    
                    fetch('/api/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: product.id,
                            quantity: 1
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
