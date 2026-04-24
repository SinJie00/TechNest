<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('images/technest-logo.png') }}" type="image/png" alt="TechNest Logo">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
            
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 md:hidden"></div>

            <!-- Sidebar -->
            <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-black text-white transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-auto min-h-screen flex flex-col">
                <div class="p-4 border-b border-slate-700 flex items-center gap-2">
                    <div class="bg-white p-1 rounded">
                        <img src="{{ asset('images/technest-logo.png') }}" alt="TechNest Logo" class="block h-12 w-auto"> <!-- Image Logo -->
                    </div>
                    <span class="text-xl font-bold tracking-wide">TechNest Admin</span>
                </div>
                
                <nav class="flex-1 py-6 space-y-2 px-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-md transition-colors duration-200 hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center px-4 py-3 rounded-md transition-colors duration-200 hover:bg-slate-800 {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Categories
                    </a>

                    <a href="{{ route('admin.subcategories.index') }}" class="flex items-center px-4 py-3 rounded-md transition-colors duration-200 hover:bg-slate-800 {{ request()->routeIs('admin.subcategories.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Subcategories
                    </a>

                    <a href="{{ route('admin.brands.index') }}" class="flex items-center px-4 py-3 rounded-md transition-colors duration-200 hover:bg-slate-800 {{ request()->routeIs('admin.brands.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Brands
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 rounded-md transition-colors duration-200 hover:bg-slate-800 {{ request()->routeIs('admin.products.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Products
                    </a>

                    <a href="{{ route('admin.orders') }}" class="flex items-center px-4 py-3 rounded-md transition-colors duration-200 hover:bg-slate-800 {{ request()->routeIs('admin.orders') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:text-white' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Orders
                    </a>
                </nav>

                <div class="p-4 border-t border-slate-700 bg-slate-800">
                    <div class="flex items-center mb-4">
                        <div class="text-sm">
                            <p class="font-medium text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-400">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-slate-600 rounded-md shadow-sm text-sm font-medium text-slate-200 bg-slate-700 hover:bg-slate-600 transition-colors">
                            Log Out
                        </button>
                    </form>
                    <a href="{{ route('home') }}" class="mt-3 block text-center text-xs text-indigo-400 hover:text-white transition-colors">Back to TechNest Store</a>
                </div>
            </div>

            <!-- Page Content -->
            <div class="flex-1 flex flex-col w-full bg-slate-900 text-white">
                <!-- Mobile Header -->
                <div class="md:hidden flex items-center justify-between bg-slate-800 p-4 shadow-md z-10 sticky top-0">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-white hover:text-gray-300 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="text-lg font-bold">TechNest Admin</span>
                    <div class="w-6"></div> <!-- Spacer -->
                </div>

                <main class="flex-1 overflow-y-auto p-8">

<!--             <main class="flex-1 overflow-y-auto p-8">
 -->                @if (isset($header))
                    <header class="bg-white shadow mb-8 rounded-lg">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                @if(session('success'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg @click="show = false" class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg @click="show = false" class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>
                @endif

                {{ $slot }}
            </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function confirmDelete(event) {
                event.preventDefault();
                const form = event.target.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',    // Tailwind gray-500
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            }
        </script>
    </body>
</html>
