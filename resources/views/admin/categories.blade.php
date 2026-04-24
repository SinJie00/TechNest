<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             {{ __('Manage Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Simplified content -->
                <p class="text-gray-500">category management is similar to products and can be implemented using the same pattern (CRUD modal + API). For this demo, please use database seeder or direct DB access.</p>
                <div class="mt-4">
                     <h3 class="font-bold">Existing Categories</h3>
                     <ul class="list-disc pl-5 mt-2" x-data="{ cats: [] }" x-init="fetch('/api/categories/all').then(r=>r.json()).then(d=>cats=d)">
                        <template x-for="cat in cats" :key="cat.id">
                            <li x-text="cat.name"></li>
                        </template>
                     </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
