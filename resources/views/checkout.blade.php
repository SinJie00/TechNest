<x-app-layout>
    <div class="bg-gray-50" x-data="checkout()" x-init="init()">
        <div class="max-w-2xl mx-auto pt-16 pb-24 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <h2 class="sr-only">Checkout</h2>

            <form class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16" @submit.prevent="placeOrder">
                <div>
                    <!-- Contact Info -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Contact information</h2>
                        <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Full name</label>
                                <input type="text" x-model="form.contact_name"
                                    :class="errors.contact_name ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                <template x-if="errors.contact_name">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.contact_name[0]"></p>
                                </template>
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Email address</label>
                                <input type="email" x-model="form.contact_email"
                                    :class="errors.contact_email ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                <template x-if="errors.contact_email">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.contact_email[0]"></p>
                                </template>
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Phone number</label>
                                <input type="text" x-model="form.contact_phone"
                                    :class="errors.contact_phone ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                <template x-if="errors.contact_phone">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.contact_phone[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Info -->
                    <div class="mt-10 border-t border-gray-200 pt-10">
                        <h2 class="text-lg font-medium text-gray-900">Shipping information</h2>

                        <template x-if="userAddresses.length > 0">
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Saved Addresses</label>
                                <select @change="selectAddress($event.target.value)"
                                    class="mt-1 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">-- Type new address below --</option>
                                    <template x-for="(addr, index) in userAddresses" :key="index">
                                        <option :value="index" x-text="addr.line1 + ', ' + addr.city"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Street address</label>
                                <input type="text" x-model="form.shipping_address"
                                    :class="errors.shipping_address ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                <template x-if="errors.shipping_address">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.shipping_address[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">State / Province</label>
                                <select x-model="form.shipping_state"
                                    :class="errors.shipping_state ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                    <option value="">Select a state</option>
                                    <template x-for="state in states" :key="state">
                                        <option :value="state" x-text="state"></option>
                                    </template>
                                </select>
                                <template x-if="errors.shipping_state">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.shipping_state[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <select x-model="form.shipping_city" :disabled="!form.shipping_state"
                                    :class="errors.shipping_city ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                                    :class="{'bg-gray-100 cursor-not-allowed': !form.shipping_state}">
                                    <option value="">Select a city</option>
                                    <template x-for="city in cities.shipping" :key="city">
                                        <option :value="city" x-text="city"></option>
                                    </template>
                                </select>
                                <template x-if="errors.shipping_city">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.shipping_city[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Postal code</label>
                                <select x-model="form.shipping_postal_code" :disabled="!form.shipping_city"
                                    :class="errors.shipping_postal_code ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                                    :class="{'bg-gray-100 cursor-not-allowed': !form.shipping_city}">
                                    <option value="">Select a postcode</option>
                                    <template x-for="postcode in postcodes.shipping" :key="postcode">
                                        <option :value="postcode" x-text="postcode"></option>
                                    </template>
                                </select>
                                <template x-if="errors.shipping_postal_code">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.shipping_postal_code[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Country</label>
                                <input type="text" x-model="form.shipping_country" readonly
                                    :class="errors.shipping_country ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm bg-gray-100 text-gray-500 cursor-not-allowed">
                                <template x-if="errors.shipping_country">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.shipping_country[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Method -->
                    <div class="mt-10 border-t border-gray-200 pt-10">
                        <h2 class="text-lg font-medium text-gray-900">Shipping method</h2>
                        <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                            <div class="relative bg-white border rounded-lg shadow-sm p-4 flex cursor-pointer focus:outline-none"
                                :class="form.shipping_method === 'home_delivery' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-300'"
                                @click="form.shipping_method = 'home_delivery'; calculateTotal()">
                                <div class="flex-1 flex">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Home Delivery</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">2-4 business
                                            days</span>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">RM 10.00</span>
                            </div>
                            <div class="relative bg-white border rounded-lg shadow-sm p-4 flex cursor-pointer focus:outline-none"
                                :class="form.shipping_method === 'store_pickup' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-300'"
                                @click="form.shipping_method = 'store_pickup'; calculateTotal()">
                                <div class="flex-1 flex">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Store Pickup</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">Available
                                            tomorrow</span>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Free</span>
                            </div>
                        </div>
                        <template x-if="errors.shipping_method">
                            <p class="mt-2 text-sm text-red-600" x-text="errors.shipping_method[0]"></p>
                        </template>
                    </div>

                    <!-- Billing Option -->
                    <div class="mt-10 border-t border-gray-200 pt-10">
                        <h2 class="text-lg font-medium text-gray-900">Billing information</h2>
                        <div class="mt-4 flex items-center">
                            <input type="checkbox" id="same_as_shipping" x-model="form.use_shipping_for_billing"
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="same_as_shipping" class="ml-2 block text-sm text-gray-900">Use shipping address
                                as billing address</label>
                        </div>

                        <div x-show="!form.use_shipping_for_billing"
                            class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Street address</label>
                                <input type="text" x-model="form.billing_address"
                                    :class="errors.billing_address ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                <template x-if="errors.billing_address">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.billing_address[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">State / Province</label>
                                <select x-model="form.billing_state"
                                    :class="errors.billing_state ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm">
                                    <option value="">Select a state</option>
                                    <template x-for="state in states" :key="state">
                                        <option :value="state" x-text="state"></option>
                                    </template>
                                </select>
                                <template x-if="errors.billing_state">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.billing_state[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <select x-model="form.billing_city" :disabled="!form.billing_state"
                                    :class="errors.billing_city ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                                    :class="{'bg-gray-100 cursor-not-allowed': !form.billing_state}">
                                    <option value="">Select a city</option>
                                    <template x-for="city in cities.billing" :key="city">
                                        <option :value="city" x-text="city"></option>
                                    </template>
                                </select>
                                <template x-if="errors.billing_city">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.billing_city[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Postal code</label>
                                <select x-model="form.billing_postal_code" :disabled="!form.billing_city"
                                    :class="errors.billing_postal_code ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                                    :class="{'bg-gray-100 cursor-not-allowed': !form.billing_city}">
                                    <option value="">Select a postcode</option>
                                    <template x-for="postcode in postcodes.billing" :key="postcode">
                                        <option :value="postcode" x-text="postcode"></option>
                                    </template>
                                </select>
                                <template x-if="errors.billing_postal_code">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.billing_postal_code[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Country</label>
                                <input type="text" x-model="form.billing_country" readonly
                                    :class="errors.billing_country ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'"
                                    class="mt-1 block w-full rounded-md shadow-sm sm:text-sm bg-gray-100 text-gray-500 cursor-not-allowed">
                                <template x-if="errors.billing_country">
                                    <p class="mt-2 text-sm text-red-600" x-text="errors.billing_country[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="mt-10 border-t border-gray-200 pt-10">
                        <h2 class="text-lg font-medium text-gray-900">Payment</h2>
                        <div class="mt-6">
                            <!-- Stripe Elements Container -->
                            <div id="card-element" class="p-4 border rounded bg-white"
                                :class="errors.payment_method ? 'border-red-300' : 'border-gray-300'">
                                <!-- Stripe Element injects here -->
                            </div>
                            <template x-if="errors.payment_method">
                                <p class="mt-2 text-sm text-red-600" x-text="errors.payment_method[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Order Summary (Right Column) -->
                <div class="mt-10 lg:mt-0">
                    <h2 class="text-lg font-medium text-gray-900">Order summary</h2>
                    <div class="mt-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="sr-only">Items in your cart</h3>

                        <template x-if="Object.keys(errors).length > 0">
                            <div class="p-4 bg-red-50 border-b border-red-200">
                                <h3 class="text-sm font-medium text-red-800">There were issues with your submission</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <template x-for="(err, key) in errors" :key="key">
                                            <li x-text="err[0]"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </template>

                        <ul role="list" class="divide-y divide-gray-200 flex-1 overflow-y-auto max-h-96">
                            <template x-for="(item, key) in cartItems" :key="key">
                                <li class="flex py-6 px-4 sm:px-6">
                                    <div class="flex-shrink-0">
                                        <img :src="item.image || 'https://via.placeholder.com/100'" alt=""
                                            class="w-20 h-20 rounded-md object-center object-cover">
                                    </div>
                                    <div class="ml-6 flex-1 flex flex-col">
                                        <div class="flex">
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-sm">
                                                    <a href="#" class="font-medium text-gray-700 hover:text-gray-800"
                                                        x-text="item.name"></a>
                                                </h4>
                                                <p class="mt-1 text-sm text-gray-500" x-text="item.variant_name"></p>
                                            </div>
                                        </div>
                                        <div class="flex-1 pt-2 flex items-end justify-between">
                                            <p class="mt-1 text-sm font-medium text-gray-900"
                                                x-text="'RM ' + item.price"></p>
                                            <div class="ml-4">
                                                <label class="sr-only">Quantity</label>
                                                <span class="text-gray-500 text-sm"
                                                    x-text="'Qty ' + item.quantity"></span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                        <dl class="border-t border-gray-200 py-6 px-4 space-y-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Subtotal</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="'RM ' + subtotal"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Shipping</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="'RM ' + shippingCost.toFixed(2)">
                                </dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                                <dt class="text-base font-medium">Total</dt>
                                <dd class="text-base font-medium text-gray-900" x-text="'RM ' + cartTotal"></dd>
                            </div>
                        </dl>

                        <div class="border-t border-gray-200 py-6 px-4 sm:px-6">
                            <button type="submit" :disabled="loading"
                                class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!loading">Confirm Order</span>
                                <span x-show="loading" class="flex items-center justify-center" style="display: none;">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Processing Payment...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/malaysia-postcodes@2.7.3/dist/malaysia-postcodes.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        function checkout() {
            return {
                cartItems: {},
                userAddresses: @json(auth()->check() ? auth()->user()->addresses : []),
                subtotal: 0,
                shippingCost: 10,
                cartTotal: 0,
                errors: {},
                states: [],
                cities: { shipping: [], billing: [] },
                postcodes: { shipping: [], billing: [] },
                form: {
                    contact_name: '{{ auth()->user()->name ?? "" }}',
                    contact_email: '{{ auth()->user()->email ?? "" }}',
                    contact_phone: '{{ auth()->user()->phone_number ?? "" }}',
                    shipping_address: '',
                    shipping_city: '',
                    shipping_state: '',
                    shipping_postal_code: '',
                    shipping_country: 'Malaysia',
                    use_shipping_for_billing: true,
                    billing_address: '',
                    billing_city: '',
                    billing_state: '',
                    billing_postal_code: '',
                    billing_country: 'Malaysia',
                    shipping_method: 'home_delivery',
                },
                stripeObj: null,
                cardElement: null,
                loading: false,
                init() {
                    this.fetchCart();

                    this.states = window.malaysiaPostcodes.getStates();

                    this.$watch('form.shipping_state', (value) => {
                        this.cities.shipping = value ? window.malaysiaPostcodes.getCities(value) : [];
                        if (!this.cities.shipping.includes(this.form.shipping_city)) this.form.shipping_city = '';
                    });
                    this.$watch('form.shipping_city', (value) => {
                        this.postcodes.shipping = (this.form.shipping_state && value) ? window.malaysiaPostcodes.getPostcodes(this.form.shipping_state, value) : [];
                        if (!this.postcodes.shipping.includes(this.form.shipping_postal_code)) this.form.shipping_postal_code = '';
                    });

                    this.$watch('form.billing_state', (value) => {
                        this.cities.billing = value ? window.malaysiaPostcodes.getCities(value) : [];
                        if (!this.cities.billing.includes(this.form.billing_city)) this.form.billing_city = '';
                    });
                    this.$watch('form.billing_city', (value) => {
                        this.postcodes.billing = (this.form.billing_state && value) ? window.malaysiaPostcodes.getPostcodes(this.form.billing_state, value) : [];
                        if (!this.postcodes.billing.includes(this.form.billing_postal_code)) this.form.billing_postal_code = '';
                    });

                    if (this.userAddresses && this.userAddresses.length > 0) {
                        this.selectAddress(0);
                    }

                    // Initialize Stripe Elements
                    this.stripeObj = Stripe('{{ env("STRIPE_KEY", "pk_test_TYooMQauvdEDq54NiTphI7jx") }}');
                    const elements = this.stripeObj.elements();

                    this.cardElement = elements.create('card', {
                        hidePostalCode: true,
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#32325d',
                            }
                        }
                    });

                    this.cardElement.mount('#card-element');

                    this.cardElement.on('change', (event) => {
                        if (event.error) {
                            this.errors.payment_method = [event.error.message];
                        } else {
                            delete this.errors.payment_method;
                        }
                    });
                },
                selectAddress(index) {
                    if (index === "") {
                        this.form.shipping_address = '';
                        this.form.shipping_city = '';
                        this.form.shipping_state = '';
                        this.form.shipping_postal_code = '';
                        return;
                    }
                    const addr = this.userAddresses[index];
                    if (addr) {
                        this.form.shipping_address = addr.line1 + (addr.line2 ? ', ' + addr.line2 : '');
                        this.form.shipping_state = addr.state;
                        // wait for alpine to update DOM dependencies due to watch
                        setTimeout(() => {
                            this.form.shipping_city = addr.city;
                            setTimeout(() => {
                                this.form.shipping_postal_code = addr.postal_code;
                            }, 50);
                        }, 50);
                    }
                },
                fetchCart() {
                    fetch('/api/cart')
                        .then(res => res.json())
                        .then(data => {
                            this.cartItems = data;
                            this.calculateTotal();
                        });
                },
                calculateTotal() {
                    let total = 0;
                    for (let key in this.cartItems) {
                        total += this.cartItems[key].price * this.cartItems[key].quantity;
                    }
                    this.subtotal = total.toFixed(2);
                    this.shippingCost = this.form.shipping_method === 'home_delivery' ? 10.00 : 0.00;
                    this.cartTotal = (total + this.shippingCost).toFixed(2);
                },
                async placeOrder() {
                    if (this.loading) return;
                    this.errors = {};
                    this.loading = true;

                    let items = [];
                    for (let key in this.cartItems) {
                        items.push({
                            product_id: this.cartItems[key].product_id,
                            variant_id: this.cartItems[key].variant_id,
                            quantity: this.cartItems[key].quantity
                        });
                    }

                    // Native Stripe Frontend Validation Boundary
                    let tokenResult = await this.stripeObj.createToken(this.cardElement, {
                        name: this.form.contact_name
                    });

                    if (tokenResult.error) {
                        this.errors.payment_method = [tokenResult.error.message];
                        this.loading = false;
                        return; // Halt form submission exactly per requirements
                    }

                    fetch('/api/checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ...this.form, stripeToken: tokenResult.token.id, items: items })
                    })
                        .then(res => {
                            window.latestCheckoutStatus = res.status;
                            return res.json().catch(() => { throw new Error('Order failed'); });
                        })
                        .then(data => {
                            this.loading = false;
                            if (window.latestCheckoutStatus === 422) {
                                this.errors = data.errors || {};
                                return;
                            }
                            if (window.latestCheckoutStatus >= 400) {
                                throw new Error(data.message || 'Order failed');
                            }
                            let itemsHtml = '<ul style="text-align: left; margin: 10px 0; padding-left: 20px;" class="text-sm border p-3 rounded bg-gray-50">';
                            if (data.items) {
                                data.items.forEach(item => {
                                    itemsHtml += `<li>${item.quantity}x ${item.product ? item.product.name : 'Product'} - RM ${parseFloat(item.price).toFixed(2)}</li>`;
                                });
                            }
                            itemsHtml += '</ul>';
                            itemsHtml += `<p style="text-align: right; font-weight: bold;">Total: RM ${parseFloat(data.total_price).toFixed(2)}</p>`;

                            window.BrandAlert.fire({
                                icon: 'success',
                                title: 'Order placed successfully!',
                                html: '<p style="margin-bottom: 15px;">Please check your email for confirmation.</p><div style="text-align: left;"><strong>Order ID: #' + data.id + '</strong></div>' + itemsHtml,
                                confirmButtonText: 'Continue Shopping',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = '/';
                            });
                        })
                        .catch(err => {
                            this.loading = false;
                            window.BrandAlert.fire({
                                icon: 'error',
                                title: 'Checkout Flow Halted',
                                text: err.message
                            });
                            this.fetchCart();
                        });
                }
            }
        }
    </script>
</x-app-layout>