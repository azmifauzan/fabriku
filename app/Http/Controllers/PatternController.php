<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatternRequest;
use App\Http\Requests\UpdatePatternRequest;
use App\Models\Material;
use App\Models\Pattern;
use Inertia\Inertia;

class PatternController extends Controller
{
    public function index()
    {
        $patterns = Pattern::query()
            ->withCount('cuttingOrders')
            ->with('materials:id,name,code,unit')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when(request('product_type'), fn ($query, $type) => $query->where('product_type', $type))
            ->when(request('is_active') !== null, fn ($query) => $query->where('is_active', request('is_active')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Patterns/Index', [
            'patterns' => $patterns,
            'filters' => request()->only(['search', 'product_type', 'is_active']),
        ]);
    }

    public function create()
    {
        $materials = Material::query()
            ->where('is_active', true)
            ->select('id', 'code', 'name', 'unit', 'standard_price', 'current_stock')
            ->orderBy('name')
            ->get();

        $tenant = auth()->user()->tenant;
        $categoryConfig = $tenant->getCategoryConfig();

        return Inertia::render('Patterns/PatternForm', [
            'materials' => $materials,
            'productTypes' => $categoryConfig['product_types'] ?? [],
            'sizes' => $categoryConfig['sizes'] ?? [],
            'isEdit' => false,
        ]);
    }

    public function store(StorePatternRequest $request)
    {
        $pattern = Pattern::create($request->safe()->except('materials'));

        // Attach materials with quantities
        if ($request->has('materials') && is_array($request->materials)) {
            foreach ($request->materials as $materialData) {
                if (! empty($materialData['material_id']) && ! empty($materialData['quantity_needed'])) {
                    $pattern->materials()->attach($materialData['material_id'], [
                        'quantity_needed' => $materialData['quantity_needed'],
                        'notes' => $materialData['notes'] ?? null,
                    ]);
                }
            }
        }

        $tenant = auth()->user()?->tenant;
        $patternLabel = $tenant?->getTerminology('pattern') ?? 'Pattern';

        return redirect()->route('patterns.index')
            ->with('success', "{$patternLabel} berhasil ditambahkan.");
    }

    public function edit(Pattern $pattern)
    {
        $pattern->load('materials.material');

        $materials = Material::query()
            ->where('is_active', true)
            ->select('id', 'code', 'name', 'unit', 'unit_price', 'stock_quantity')
            ->orderBy('name')
            ->get();

        $tenant = auth()->user()->tenant;
        $categoryConfig = $tenant->getCategoryConfig();

        return Inertia::render('Patterns/PatternForm', [
            'pattern' => $pattern,
            'materials' => $materials,
            'productTypes' => $categoryConfig['product_types'] ?? [],
            'sizes' => $categoryConfig['sizes'] ?? [],
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePatternRequest $request, Pattern $pattern)
    {
        $pattern->update($request->safe()->except('materials'));

        // Sync materials
        if ($request->has('materials')) {
            $syncData = [];
            foreach ($request->materials as $materialData) {
                if (! empty($materialData['material_id']) && ! empty($materialData['quantity_needed'])) {
                    $syncData[$materialData['material_id']] = [
                        'quantity_needed' => $materialData['quantity_needed'],
                        'notes' => $materialData['notes'] ?? null,
                    ];
                }
            }
            $pattern->materials()->sync($syncData);
        }

        $tenant = auth()->user()?->tenant;
        $patternLabel = $tenant?->getTerminology('pattern') ?? 'Pattern';

        return redirect()->route('patterns.index')
            ->with('success', "{$patternLabel} berhasil diperbarui.");
    }

    public function destroy(Pattern $pattern)
    {
        $tenant = auth()->user()?->tenant;
        $patternLabel = $tenant?->getTerminology('pattern') ?? 'Pattern';
        $prepOrderLabel = $tenant?->getTerminology('preparation_order') ?? 'Cutting order';

        if ($pattern->cuttingOrders()->exists()) {
            return back()->withErrors(['pattern' => "{$patternLabel} tidak bisa dihapus karena sudah digunakan di {$prepOrderLabel}."]);
        }

        $pattern->delete();

        return redirect()->route('patterns.index')
            ->with('success', "{$patternLabel} berhasil dihapus.");
    }
}
