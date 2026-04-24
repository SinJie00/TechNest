<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $slug = Str::slug($request->name);
        // Ensure slug is unique if needed, or rely on name uniqueness.

        $data = [
            'name' => $request->name,
            'slug' => $slug,
        ];

        if ($request->hasFile('logo')) {
            // Build readable + unique filename
            $filename = Str::slug($request->name).'-'.time().'.'.
                        $request->file('logo')->getClientOriginalExtension();

            // Store file in /storage/app/public/brands
            $path = $request->file('logo')->storeAs('brands', $filename, 'public');

            // Save relative path in DB (e.g. "brands/apple-1737432000.png")
            $data['logo'] = $path;
        }

        Brand::create($data);

        $total = Brand::count();
        $lastPage = ceil($total / 10);

        return redirect()->route('admin.brands.index', ['page' => $lastPage])->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,'.$id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $brand = Brand::findOrFail($id);

        // Generate slug from name
        $slug = Str::slug($request->name);

        $data = [
            'name' => $request->name,
            'slug' => $slug,
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            // Build readable + unique filename
            $filename = Str::slug($request->name).'-'.time().'.'.
                        $request->file('logo')->getClientOriginalExtension();

            // Store file in /storage/app/public/brands
            $path = $request->file('logo')->storeAs('brands', $filename, 'public');

            // Save relative path in DB (e.g. "brands/apple-1737432000.png")
            $data['logo'] = $path;
        }

        $brand->update($data);

        return redirect()
            ->route('admin.brands.index', ['page' => $request->input('page')])
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
