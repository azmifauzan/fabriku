<?php

namespace App\Http\Controllers;

use App\Models\MaterialType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaterialTypeController extends Controller
{
    public function index()
    {
        $materialTypes = MaterialType::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when(request('is_active') !== null, fn ($query) => $query->where('is_active', request('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('MaterialTypes/Index', [
            'materialTypes' => $materialTypes,
            'filters' => request()->only(['search', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('MaterialTypes/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:material_types,code,NULL,id,tenant_id,'.auth()->user()->tenant_id],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        MaterialType::create($validated);

        return redirect()->route('material-types.index')
            ->with('success', 'Jenis bahan berhasil ditambahkan.');
    }

    public function edit(MaterialType $materialType)
    {
        return Inertia::render('MaterialTypes/Form', [
            'materialType' => $materialType,
        ]);
    }

    public function update(Request $request, MaterialType $materialType)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:material_types,code,'.$materialType->id.',id,tenant_id,'.auth()->user()->tenant_id],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $materialType->update($validated);

        return redirect()->route('material-types.index')
            ->with('success', 'Jenis bahan berhasil diupdate.');
    }

    public function destroy(MaterialType $materialType)
    {
        // Check if any materials use this type
        if (\App\Models\Material::where('material_type_id', $materialType->id)->exists()) {
            return back()->with('error', 'Jenis bahan tidak bisa dihapus karena masih digunakan oleh bahan baku.');
        }

        $materialType->delete();

        return redirect()->route('material-types.index')
            ->with('success', 'Jenis bahan berhasil dihapus.');
    }
}
