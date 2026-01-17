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
            ->withQueryString()
            ->through(fn ($material) => [
                ...$material->toArray(),
                'materialType' => $material->materialType?->only(['id', 'name', 'code']),
            ]);

        $types = MaterialType::query()
            ->select('id', 'name')
            ->where('is_active', true)
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
            ->get(['id', 'code', 'name']);

        return Inertia::render('Materials/Form', [
            'materialTypes' => $materialTypes,
        ]);
    }

    public function store(StoreMaterialRequest $request)
    {
        $data = $request->safe()->except(['attributes', 'stock_quantity']);

        // Map stock_quantity to actual field name if needed
        if ($request->has('stock_quantity')) {
            $data['stock_quantity'] = $request->stock_quantity;
        }

        $material = Material::create($data);

        // Save attributes if provided
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attr) {
                if (! empty($attr['name']) && ! empty($attr['value'])) {
                    $material->materialAttributes()->create([
                        'attribute_key' => $attr['name'],
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
        $material->load(['materialAttributes', 'materialType', 'receipts' => fn ($query) => $query->latest()->take(10)]);

        return Inertia::render('Materials/Show', [
            'material' => [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'material_type_id' => $material->material_type_id,
                'unit' => $material->unit,
                'price_per_unit' => (string) $material->price_per_unit,
                'stock_quantity' => (string) $material->stock_quantity,
                'min_stock' => (string) $material->min_stock,
                'supplier_name' => $material->supplier_name,
                'description' => $material->description,
                'materialType' => $material->materialType?->only(['id', 'name', 'code']),
                'attributes' => $material->materialAttributes->map(fn ($attr) => [
                    'attribute_name' => $attr->attribute_key,
                    'attribute_value' => $attr->attribute_value,
                ])->toArray(),
            ],
        ]);
    }

    public function edit(Material $material)
    {
        $material->load(['materialAttributes', 'materialType']);

        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Materials/Form', [
            'material' => [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'material_type_id' => $material->material_type_id,
                'unit' => $material->unit,
                'price_per_unit' => (string) $material->price_per_unit,
                'stock_quantity' => (string) $material->stock_quantity,
                'min_stock' => (string) $material->min_stock,
                'supplier_name' => $material->supplier_name,
                'description' => $material->description,
                'attributes' => $material->materialAttributes->map(fn ($attr) => [
                    'attribute_name' => $attr->attribute_key,
                    'attribute_value' => $attr->attribute_value,
                ])->toArray(),
            ],
            'materialTypes' => $materialTypes,
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->safe()->except(['attributes', 'stock_quantity']);

        // Map stock_quantity to actual field name if needed
        if ($request->has('stock_quantity')) {
            $data['stock_quantity'] = $request->stock_quantity;
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
                            'attribute_key' => $attr['name'],
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
