<x-app-layout>
    <div class="bg-white">
        <div class="max-w-2xl mx-auto pt-16 pb-24 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Shopping Cart</h1>
            <div class="mt-12 lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start xl:gap-x-16">
                <!-- Shopping Cart items is basically the same logic as popup, can be reused or independent -->
                <!-- Ideally we componentize the cart list. For now, simple duplication or redirect. -->
                <!-- I'll just put a message saying use the popup or redirect to shop, OR implement simple view. -->
                <section aria-labelledby="cart-heading" class="lg:col-span-7">
                    <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>
                    <!-- Re-using cart popup logic via Alpine store would be best. -->
                    <!-- For this task, I'll direct them to checkout or show empty state if JS not loaded. -->
                    <div x-data="cart()" x-init="init()" class="space-y-6">
                         <template x-for="(item, key) in items" :key="key">
                            <div class="flex py-6 border-b border-gray-200">
                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                    <img :src="item.image || 'https://via.placeholder.com/150'" alt="" class="h-full w-full object-cover object-center">
                                </div>
                                <div class="ml-4 flex flex-1 flex-col">
                                    <div>
                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                            <h3><a href="#" x-text="item.name"></a></h3>
                                            <p class="ml-4" x-text="'RM ' + formatPrice(item.price * item.quantity)"></p>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500" x-show="item.variant_name" x-text="item.variant_name"></p>
                                        <p class="mt-1 text-xs text-red-600 max-w-[200px]" x-show="item.warning" x-text="item.warning"></p>
                                    </div>
                                    <div class="flex flex-1 items-end justify-between text-sm">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" @click="updateQuantity(key, item.quantity - 1, item.max_stock)" :disabled="item.quantity <= 1" class="text-gray-500 hover:text-gray-700 disabled:opacity-50 border px-2 py-1 rounded">-</button>
                                            <span class="text-gray-500" x-text="item.quantity"></span>
                                            <button type="button" @click="updateQuantity(key, item.quantity + 1, item.max_stock)" :disabled="item.quantity >= item.max_stock" class="text-gray-500 hover:text-gray-700 disabled:opacity-50 border px-2 py-1 rounded">+</button>
                                        </div>
                                        <div class="flex">
                                            <button type="button" class="font-medium text-indigo-600 hover:text-indigo-500" @click="confirmRemove(key)">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="Object.keys(items).length === 0" class="text-center py-12">
                            <p class="text-gray-500">Your cart is empty.</p>
                            <a href="{{ route('shop') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">Continue Shopping</a>
                        </div>
                    </div>
                </section>

                <!-- Order summary -->
                <section aria-labelledby="summary-heading" class="mt-16 bg-gray-50 rounded-lg px-4 py-6 sm:p-6 lg:p-8 lg:mt-0 lg:col-span-5">
                    <h2 id="summary-heading" class="text-lg font-medium text-gray-900">Order summary</h2>
                    <div x-data="cart()" x-init="init()">
                         <dl class="mt-6 space-y-4">
                            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                                <dt class="text-base font-medium text-gray-900">Order total</dt>
                                <dd class="text-base font-medium text-gray-900" x-text="'RM ' + cartTotal()"></dd>
                            </div>
                        </dl>
                        <div class="mt-6">
                            <a href="{{ route('checkout') }}" class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500 flex items-center justify-center">Checkout</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
