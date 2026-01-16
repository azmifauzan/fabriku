<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\MaterialType;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::query()
            ->with(['materialAttributes', 'materialType'])
            ->withCount('receipts')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when(request('material_type_id'), fn ($query, $typeId) => $query->where('material_type_id', $typeId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $types = MaterialType::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'types' => $types,
            'filters' => request()->only(['search', 'material_type_id']),
        ]);
    }

    public function create()
    {
        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['code', 'name']);

        return Inertia::render('Materials/Form', [
            'materialTypes' => $materialTypes,
        ]);
    }

    public function store(StoreMaterialRequest $request)
    {
        $data = $request->safe()->except(['attributes', 'quantity']);

        // Map quantity to current_stock
        if ($request->has('quantity')) {
            $data['current_stock'] = $request->quantity;
        }

        $material = Material::create($data);

        // Save attributes if provided
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attr) {
                if (! empty($attr['name']) && ! empty($attr['value'])) {
                    $material->materialAttributes()->create([
                        'attribute_name' => $attr['name'],
                        'attribute_value' => $attr['value'],
                    ]);
                }
            }
        }

        return redirect()->route('materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    public function show(Material $material)
    {
        $material->load(['receipts' => fn ($query) => $query->latest()->take(10)]);

        return Inertia::render('Materials/Show', [
            'material' => $material,
        ]);
    }

    public function edit(Material $material)
    {
        $material->load('materialAttributes');

        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['code', 'name']);

        return Inertia::render('Materials/Form', [
            'material' => $material,
            'materialTypes' => $materialTypes,
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->safe()->except(['attributes', 'quantity']);

        // Map quantity to current_stock
        if ($request->has('quantity')) {
            $data['current_stock'] = $request->quantity;
        }

        $material->update($data);

        // Sync attributes
        if ($request->has('attributes')) {
            // Delete old attributes
            $material->materialAttributes()->delete();

            // Create new attributes
            if (is_array($request->attributes)) {
                foreach ($request->attributes as $attr) {
                    if (! empty($attr['name']) && ! empty($attr['value'])) {
                        $material->materialAttributes()->create([
                            'attribute_name' => $attr['name'],
                            'attribute_value' => $attr['value'],
                        ]);
                    }
                }
            }
        }

        return redirect()->route('materials.index')
            ->with('success', 'Material berhasil diupdate.');
    }

    public function destroy(Material $material)
    {
        if ($material->receipts()->exists()) {
            return back()->with('error', 'Material tidak bisa dihapus karena sudah memiliki penerimaan.');
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }
}
