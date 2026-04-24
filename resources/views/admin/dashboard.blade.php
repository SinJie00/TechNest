<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>


    <div class="py-6" x-data="adminDashboard()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
             <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Sales</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="'RM ' + (stats.total_sales || 0)"></dd>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="stats.total_orders"></dd>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Products</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="stats.total_products"></dd>
                            </div>
                        </div>
                    </div>
                </div>
                 
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                       <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                       <dd class="text-lg font-medium text-gray-900" x-text="stats.total_users"></dd>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white overflow-hidden shadow sm:rounded-lg mb-8">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Orders</h3>
                </div>
                 <div class="overflow-x-auto" x-show="stats.recent_orders && stats.recent_orders.length > 0">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             <template x-for="order in stats.recent_orders" :key="order.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="order.id"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="order.user ? order.user.name : 'Guest'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="'RM ' + order.total_price"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="order.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" x-text="order.status"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="new Date(order.created_at).toLocaleDateString()"></td>
                                </tr>
                             </template>
                        </tbody>
                    </table>
                 </div>
                 <div x-show="!stats.recent_orders || stats.recent_orders.length === 0" class="p-5 text-center text-gray-500">
                     No recent orders found.
                 </div>
            </div>
            

        </div>
    </div>
    
    <script>
        function adminDashboard() {
            return {
                stats: {},
                init() {
                    fetch('/api/admin/dashboard', {
                         headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming token storage mechanism or session cookie works (Breeze uses session cookie for web routes, API routes might need explicit token if not passing cookies? Actually Breeze web routes use session, so cookie is sent automatically. API routes if guarded by sanctum can use session cookie if calling from same domain/SPA.)
                            // My api.php has `auth:sanctum`.
                            // If using Blade + Axios/Fetch from same domain, Laravel Sanctum checks session cookie for web guard first if configured?
                            // Default Breeze `auth` middleware handles web session.
                            // My Admin routes in API are `auth:sanctum` + `IsAdmin`.
                            // If I access them from Blade, I rely on session cookie.
                            // Ensure `EnsureFrontendRequestsAreStateful` middleware is in `api` group (it is by default in Laravel 11/Sanctum).
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.stats = data;
                    });
                }
            }
        }
    </script>
</x-admin-layout>
