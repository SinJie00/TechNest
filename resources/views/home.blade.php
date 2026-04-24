<x-app-layout>
    <!-- Hero Section -->
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Tech Gadgets</span>
                            <span class="block text-indigo-600 xl:inline">for the Future</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            Discover the latest in technology. From smartphones to smart home devices, we have everything you need to upgrade your life.
                        </p>
                        
                        <!-- Search Bar -->
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start" x-data="search()" @click.away="results = []">
                            <div class="relative rounded-md shadow-sm w-full max-w-lg">
                                <input type="text" x-model="query" @input.debounce.300ms="performSearch()" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-4 pr-12 sm:text-sm border-gray-300 rounded-md py-3" placeholder="Search for products...">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                
                                <!-- Autocomplete Results -->
                                <div x-show="results.length > 0" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                    <template x-for="result in results" :key="result.id">
                                        <a :href="'/product/' + result.id" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white block text-gray-900 group">
                                            <span class="block truncate" x-text="result.name"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 group-hover:text-white" x-html="getPriceHtml(result)"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
            <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80" alt="">
        </div>
    </div>

    <!-- Featured Products -->
    <div class="bg-gray-100" x-data="products()" x-init="fetchLatest()">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Latest Arrivals</h2>

            <div class="mt-6 grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                <template x-for="product in items" :key="product.id">
                    <div class="group relative bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition">
                        <div class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none">
                            <img :src="product.images && product.images.length ? (product.images[0].image_url.startsWith('http') ? product.images[0].image_url : '/storage/' + product.images[0].image_url) : 'https://via.placeholder.com/300'" alt="" class="w-full h-full object-center object-cover lg:w-full lg:h-full">
                        </div>
                        <div class="mt-4 flex justify-between">
                            <div>
                                <h3 class="text-sm text-gray-700">
                                    <a :href="'/product/' + product.id">
                                        <span aria-hidden="true" class="absolute inset-0"></span>
                                        <span x-text="product.name"></span>
                                    </a>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500" x-text="product.category ? product.category.name : ''"></p>
                            </div>
                            <p class="text-sm font-medium text-gray-900" x-html="getPriceHtml(product)"></p>
                        </div>
                         <!-- Stock Badge -->
                        <div class="mt-2">
                             <template x-if="product.stock > 10">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">In Stock</span>
                             </template>
                             <template x-if="product.stock <= 10 && product.stock > 0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" x-text="'Only ' + product.stock + ' left!'"></span>
                             </template>
                             <template x-if="product.stock <= 0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                             </template>
                        </div>
                    </div>
                </template>
            </div>
             <div class="mt-12 text-center">
                <a href="{{ route('shop') }}" class="inline-block bg-indigo-600 border border-transparent py-3 px-8 rounded-md font-medium text-white hover:bg-indigo-700">View All Products</a>
            </div>
        </div>
    </div>

    <script>
        function search() {
            return {
                query: '',
                results: [],
                performSearch() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }
                    fetch(`/api/products/search?query=${this.query}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data;
                        });
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
                        return `RM ${minPrice.toFixed(2)}`;
                    }
                    return `From RM ${minPrice.toFixed(2)}`;
                }
            }
        }

        function products() {
            return {
                items: [],
                fetchLatest() {
                    fetch('/api/products') // default sorts or filters
                        .then(res => res.json())
                        .then(data => {
                            this.items = data.data.slice(0, 8); // simplified, assuming paginated response
                        });
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
                        return `RM ${minPrice.toFixed(2)}`;
                    }
                    return `From RM ${minPrice.toFixed(2)}`;
                }
            }
        }
    </script>
</x-app-layout>
