<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::query()
            ->with('materialAttributes')
            ->withCount('receipts')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            })
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('is_active') !== null, fn ($query) => $query->where('is_active', request('is_active')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $types = Material::query()
            ->select('type')
            ->distinct()
            ->pluck('type');

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'types' => $types,
            'filters' => request()->only(['search', 'type', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Materials/Form');
    }

    public function store(StoreMaterialRequest $request)
    {
        $material = Material::create($request->safe()->except('attributes'));

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

        return Inertia::render('Materials/Form', [
            'material' => $material,
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $material->update($request->safe()->except('attributes'));

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
