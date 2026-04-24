<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="adminOrders()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="order in orders.data" :key="order.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                        x-text="order.id"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        x-text="order.user ? order.user.name : 'Guest'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        x-text="'RM ' + parseFloat(order.total_price).toFixed(2)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800': order.status === 'paid',
                                                'bg-blue-100 text-blue-800': order.status === 'shipped',
                                                'bg-indigo-100 text-indigo-800': order.status === 'delivered',
                                                'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                                'bg-red-100 text-red-800': order.status === 'cancelled'
                                            }"
                                            x-text="order.status"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <select @change="updateStatus(order.id, $event.target.value)"
                                                class="text-sm border-gray-300 rounded-md shadow-sm text-gray-900">
                                                <option value="pending" :selected="order.status === 'pending'">Pending
                                                </option>
                                                <option value="paid" :selected="order.status === 'paid'">Paid</option>
                                                <option value="shipped" :selected="order.status === 'shipped'">Shipped
                                                </option>
                                                <option value="delivered" :selected="order.status === 'delivered'">Delivered
                                                </option>
                                                <option value="cancelled" :selected="order.status === 'cancelled'">Cancelled
                                                </option>
                                            </select>
                                            <button @click="openModal(order)" type="button" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-700 transition">View</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="orders.data && orders.data.length === 0">
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No
                                    orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div x-show="selectedOrder" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="selectedOrder = null"></div>
                <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div x-show="selectedOrder">
                            <div class="mb-4 pb-4 border-b">
                                <h3 class="text-xl leading-6 font-semibold text-gray-900">Order #<span x-text="selectedOrder ? selectedOrder.id : ''"></span></h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Status: <span class="font-bold uppercase text-indigo-600" x-text="selectedOrder ? selectedOrder.status : ''"></span> 
                                    | Total: <span class="font-bold text-gray-900">RM <span x-text="selectedOrder ? parseFloat(selectedOrder.total_price).toFixed(2) : ''"></span></span>
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-6 text-sm mb-6">
                                <div class="bg-gray-50 p-4 rounded-md border">
                                    <h4 class="font-bold text-gray-800 border-b pb-2 mb-3">Customer Info</h4>
                                    <p class="mb-1 text-gray-700"><span class="font-medium text-gray-900">Name:</span> <span x-text="selectedOrder ? (selectedOrder.contact_name || (selectedOrder.user ? selectedOrder.user.name : 'N/A')) : ''"></span></p>
                                    <p class="mb-1 text-gray-700"><span class="font-medium text-gray-900">Email:</span> <span x-text="selectedOrder ? (selectedOrder.contact_email || (selectedOrder.user ? selectedOrder.user.email : 'N/A')) : ''"></span></p>
                                    <p class="mb-1 text-gray-700"><span class="font-medium text-gray-900">Phone:</span> <span x-text="selectedOrder ? (selectedOrder.contact_phone || 'N/A') : ''"></span></p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-md border">
                                    <h4 class="font-bold text-gray-800 border-b pb-2 mb-3">Shipping Details</h4>
                                    <p class="mb-1 text-gray-700"><span class="font-medium text-gray-900">Method:</span> <span class="capitalize" x-text="selectedOrder && selectedOrder.shipping_method ? selectedOrder.shipping_method.replace('_', ' ') : 'N/A'"></span> (RM <span x-text="selectedOrder ? parseFloat(selectedOrder.shipping_fee || 0).toFixed(2) : '0.00'"></span>)</p>
                                    <p class="mt-2 text-gray-700 font-medium tracking-tight leading-snug" x-text="selectedOrder ? selectedOrder.shipping_address : ''"></p>
                                </div>
                            </div>
                            <div class="border-t pt-4">
                                <h4 class="font-bold text-gray-800 mb-3">Purchased Items</h4>
                                <div class="bg-gray-50 p-3 rounded-md border">
                                    <ul>
                                        <template x-for="item in (selectedOrder ? selectedOrder.items : [])" :key="item.id">
                                            <li class="py-3">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <span class="text-gray-900">
                                                            <span class="font-semibold text-indigo-600 mr-2" x-text="item.quantity + 'x'"></span> 
                                                            <span class="font-medium" x-text="item.product ? item.product.name : 'Unknown Product'"></span>
                                                        </span>
                                                        <!-- Variant Attributes -->
                                                        <template x-if="item.variant && item.variant.attributes">
                                                            <div class="mt-1 flex flex-wrap gap-1">
                                                                <template x-for="(value, key) in (typeof item.variant.attributes === 'string' ? JSON.parse(item.variant.attributes) : item.variant.attributes)" :key="key">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                                        <span class="text-indigo-400 mr-1" x-text="key + ':'"></span>
                                                                        <span x-text="value"></span>
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <!-- SKU -->
                                                        <template x-if="item.variant && item.variant.sku">
                                                            <p class="mt-1 text-xs text-gray-500" x-text="'SKU: ' + item.variant.sku"></p>
                                                        </template>
                                                    </div>
                                                    <span class="font-semibold text-gray-900 ml-4">RM <span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <!-- Price Breakdown -->
                                <div class="mt-4 text-sm space-y-2">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Subtotal</span>
                                        <span class="font-medium text-gray-900" x-text="'RM ' + (selectedOrder ? (parseFloat(selectedOrder.total_price) - parseFloat(selectedOrder.shipping_fee || 0)).toFixed(2) : '0.00')"></span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span>Delivery Fee</span>
                                        <span class="font-medium text-gray-900" x-text="'RM ' + (selectedOrder ? parseFloat(selectedOrder.shipping_fee || 0).toFixed(2) : '0.00')"></span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-gray-300">
                                        <span class="font-bold text-gray-900">Total Paid</span>
                                        <span class="font-bold text-gray-900 text-base" x-text="'RM ' + (selectedOrder ? parseFloat(selectedOrder.total_price).toFixed(2) : '0.00')"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="selectedOrder = null" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminOrders() {
            return {
                orders: {},
                selectedOrder: null,
                init() {
                    this.fetchOrders();
                },
                openModal(order) {
                    this.selectedOrder = order;
                },
                fetchOrders() {
                    fetch('/api/admin/orders')
                        .then(res => res.json())
                        .then(data => {
                            this.orders = data;
                        });
                },
                updateStatus(id, status) {
                    fetch(`/api/admin/orders/${id}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.fetchOrders(); // refresh
                        });
                }
            }
        }
    </script>
</x-admin-layout>