<x-app-layout>
    <div class="bg-white">
        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">Our Brands</h1>
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">Shop from the world's most trusted manufacturers and innovators.</p>
        </div>

        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-3 lg:grid-cols-4 lg:gap-12">
                @forelse($brands as $brand)
                    <a href="{{ route('shop') }}?brand_id={{ $brand->id }}" class="group relative flex flex-col items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-xl hover:border-indigo-500 hover:-translate-y-1">
                        <!-- Brand Logo -->
                        <div class="w-24 h-24 mb-6 flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden group-hover:bg-white transition-colors duration-300">
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="max-w-full max-h-full object-contain p-2">
                            @else
                                <!-- High-quality Placeholder Logo -->
                                <div class="w-full h-full flex items-center justify-center bg-indigo-50 border border-indigo-100 rounded-xl">
                                    <span class="text-3xl font-black text-indigo-300 uppercase">{{ substr($brand->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Brand Name -->
                        <div class="text-center">
                            <span class="block text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors duration-300">{{ $brand->name }}</span>
                            <!-- Shop Now Prompt (Optional but improves UX) -->
                            <p class="mt-2 text-xs font-semibold text-indigo-500 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all duration-300">EXPLORE PRODUCTS</p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-24 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No brands found</h3>
                        <p class="mt-1 text-sm text-gray-500">Wait for your shop to populate with manufacturers.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
