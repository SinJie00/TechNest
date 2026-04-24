<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Orders') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="myOrders()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Empty State -->
            <div x-show="orders.length === 0 && !loading" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Start shopping and your orders will appear here.</p>
                    <div class="mt-6">
                        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Browse Products</a>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Orders List -->
            <div class="space-y-6" x-show="orders.length > 0">
                <template x-for="order in orders" :key="order.id">
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                        <!-- Order Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center space-x-6 text-sm">
                                    <div>
                                        <span class="text-gray-500">Order</span>
                                        <span class="ml-1 font-semibold text-gray-900" x-text="'#' + order.id"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Date</span>
                                        <span class="ml-1 font-medium text-gray-900" x-text="new Date(order.created_at).toLocaleDateString('en-MY', { year: 'numeric', month: 'long', day: 'numeric' })"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total</span>
                                        <span class="ml-1 font-semibold text-gray-900" x-text="'RM ' + parseFloat(order.total_price).toFixed(2)"></span>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide"
                                        :class="{
                                            'bg-green-100 text-green-800': order.status === 'paid',
                                            'bg-blue-100 text-blue-800': order.status === 'shipped',
                                            'bg-indigo-100 text-indigo-800': order.status === 'delivered',
                                            'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                            'bg-red-100 text-red-800': order.status === 'cancelled'
                                        }"
                                        x-text="order.status"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <ul class="divide-y divide-gray-200">
                            <template x-for="item in order.items" :key="item.id">
                                <li class="px-6 py-5">
                                    <div class="flex items-start space-x-5">
                                        <!-- Product Image -->
                                        <div class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                                            <img 
                                                :src="getItemImage(item)"
                                                :alt="item.product ? item.product.name : 'Product'"
                                                class="w-full h-full object-cover"
                                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%23cbd5e1%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z%22/%3E%3C/svg%3E'">
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-base font-semibold text-gray-900">
                                                        <a :href="'/product/' + item.product_id" class="hover:text-indigo-600 transition" x-text="item.product ? item.product.name : 'Product'"></a>
                                                    </h4>

                                                    <!-- Variant Attributes (Color, Size, etc.) -->
                                                    <template x-if="item.variant && item.variant.attributes">
                                                        <div class="mt-1 flex flex-wrap gap-2">
                                                            <template x-for="(value, key) in (typeof item.variant.attributes === 'string' ? JSON.parse(item.variant.attributes) : item.variant.attributes)" :key="key">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                                    <span class="text-gray-400 mr-1" x-text="key + ':'"></span>
                                                                    <span x-text="value"></span>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    <!-- SKU if available -->
                                                    <template x-if="item.variant && item.variant.sku">
                                                        <p class="mt-1 text-xs text-gray-400" x-text="'SKU: ' + item.variant.sku"></p>
                                                    </template>

                                                    <p class="mt-1 text-sm text-gray-500">
                                                        Qty: <span class="font-medium" x-text="item.quantity"></span>
                                                    </p>
                                                </div>

                                                <!-- Price -->
                                                <div class="text-right ml-4">
                                                    <p class="text-base font-semibold text-gray-900" x-text="'RM ' + parseFloat(item.price).toFixed(2)"></p>
                                                    <template x-if="item.quantity > 1">
                                                        <p class="text-xs text-gray-400 mt-1" x-text="'RM ' + parseFloat(item.price / item.quantity).toFixed(2) + ' each'"></p>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <!-- Order Footer -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <span x-text="order.items.length"></span> item(s)
                                    <template x-if="order.shipping_fee > 0">
                                        <span class="ml-3">· Shipping: <span class="font-medium text-gray-700" x-text="'RM ' + parseFloat(order.shipping_fee).toFixed(2)"></span></span>
                                    </template>
                                    <template x-if="order.shipping_fee == 0">
                                        <span class="ml-3">· <span class="text-green-600 font-medium">Free Pickup</span></span>
                                    </template>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-gray-500">Order Total:</span>
                                    <span class="ml-2 text-lg font-bold text-gray-900" x-text="'RM ' + parseFloat(order.total_price).toFixed(2)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>

    <script>
        function myOrders() {
            return {
                orders: [],
                loading: true,
                init() {
                    fetch('/api/orders')
                        .then(r => r.json())
                        .then(data => {
                            this.orders = data;
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                },
                getItemImage(item) {
                    // Priority 1: Variant images
                    if (item.variant && item.variant.images) {
                        let images = typeof item.variant.images === 'string' ? JSON.parse(item.variant.images) : item.variant.images;
                        if (Array.isArray(images) && images.length > 0) {
                            return '/storage/' + images[0];
                        }
                    }
                    // Priority 2: Product main images
                    if (item.product && item.product.images && item.product.images.length > 0) {
                        return '/storage/' + item.product.images[0].path;
                    }
                    // Fallback: placeholder
                    return 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%23cbd5e1%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z%22/%3E%3C/svg%3E';
                }
            }
        }
    </script>
</x-app-layout>
