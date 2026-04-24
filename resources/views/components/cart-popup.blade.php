<div x-data="{ open: false }" @cart-open.window="open = true" @keydown.escape.window="open = false" style="display: none;" x-show="open" class="relative z-50">
    <!-- Backdrop -->
    <div x-show="open" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="open" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl" x-data="cart()" x-init="init()">
                        <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900">Shopping cart</h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button type="button" class="relative -m-2 p-2 text-gray-400 hover:text-gray-500" @click="open = false">
                                        <span class="absolute -inset-0.5"></span>
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-8">
                                <div class="flow-root">
                                    <ul role="list" class="-my-6 divide-y divide-gray-200">
                                        <template x-for="(item, key) in items" :key="key">
                                            <li class="flex py-6">
                                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                                    <img :src="item.image || 'https://via.placeholder.com/150'" alt="" class="h-full w-full object-cover object-center">
                                                </div>

                                                <div class="ml-4 flex flex-1 flex-col">
                                                    <div>
                                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                                            <h3>
                                                                <a href="#" x-text="item.name"></a>
                                                            </h3>
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
                                            </li>
                                        </template>
                                        <li x-show="Object.keys(items).length === 0" class="py-6 text-center text-gray-500">
                                            Your cart is empty.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 px-4 py-6 sm:px-6">
                            <div class="flex justify-between text-base font-medium text-gray-900">
                                <p>Subtotal</p>
                                <p x-text="'RM ' + cartTotal()"></p>
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
                            <div class="mt-6">
                                <a href="{{ route('checkout') }}" class="flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700">Checkout</a>
                            </div>
                            <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                                <p>
                                    or
                                    <button type="button" class="font-medium text-indigo-600 hover:text-indigo-500" @click="open = false">
                                        Continue Shopping
                                        <span aria-hidden="true"> &rarr;</span>
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function cart() {
        return {
            items: {},
            init() {
                this.fetchCart();
                window.addEventListener('cart-updated', () => {
                   this.fetchCart(); 
                });
            },
            fetchCart() {
                fetch('/api/cart', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Failed');
                    return res.json();
                })
                .then(data => {
                    if (Array.isArray(data) || typeof data === 'object') {
                         this.items = data;
                    } else {
                         this.items = {};
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.items = {};
                });
            },
            removeItem(key) {
                fetch('/api/cart/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ key: key })
                })
                .then(res => res.json())
                .then(data => {
                    this.items = data.cart || data;
                });
            },
            confirmRemove(key) {
                window.BrandAlert.fire({
                    title: 'Are you sure?',
                    text: "Do you want to remove this item from your cart?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.removeItem(key);
                    }
                });
            },
            updateQuantity(key, newQuantity, maxStock) {
                if(newQuantity < 1) newQuantity = 1;
                if(maxStock !== undefined && newQuantity > maxStock) newQuantity = maxStock;
                
                this.items[key].quantity = newQuantity;

                fetch('/api/cart/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ key: key, quantity: newQuantity })
                })
                .then(res => res.json())
                .then(data => {
                    this.items = data;
                });
            },
            cartTotal() {
                let total = 0;
                for (const key in this.items) {
                    total += this.items[key].price * this.items[key].quantity;
                }
                return this.formatPrice(total);
            },
            formatPrice(price) {
                return (Number(price)).toFixed(2);
            }
        }
    }
</script>
