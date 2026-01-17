<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractorRequest;
use App\Http\Requests\UpdateContractorRequest;
use App\Models\Contractor;
use Inertia\Inertia;

class ContractorController extends Controller
{
    public function index()
    {
        $contractors = Contractor::query()
            ->withCount('productionOrders')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('status') !== null && request('status') !== '', fn ($query) => $query->where('is_active', (bool) request('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Contractors/Index', [
            'contractors' => $contractors,
            'filters' => request()->only(['search', 'type', 'specialty', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Contractors/Form');
    }

    public function store(StoreContractorRequest $request)
    {
        Contractor::create($request->validated());

        $tenant = auth()->user()?->tenant;
        $contractorLabel = $tenant?->getTerminology('contractor') ?? 'Kontraktor';

        return redirect()->route('contractors.index')
            ->with('success', "{$contractorLabel} berhasil ditambahkan.");
    }

    public function show(Contractor $contractor)
    {
        $contractor->load(['productionOrders' => fn ($query) => $query->latest()->take(10)]);

        $stats = [
            'total_productions' => $contractor->productionOrders()->count(),
            'total_items_produced' => $contractor->productionOrders()->sum('quantity_ordered'),
        ];

        return Inertia::render('Contractors/Show', [
            'contractor' => $contractor,
            'stats' => $stats,
        ]);
    }

    public function edit(Contractor $contractor)
    {
        return Inertia::render('Contractors/Form', [
            'contractor' => $contractor,
        ]);
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor)
    {
        $contractor->update($request->validated());

        $tenant = auth()->user()?->tenant;
        $contractorLabel = $tenant?->getTerminology('contractor') ?? 'Kontraktor';

        return redirect()->route('contractors.index')
            ->with('success', "{$contractorLabel} berhasil diperbarui.");
    }

    public function destroy(Contractor $contractor)
    {
        $tenant = auth()->user()?->tenant;
        $contractorLabel = $tenant?->getTerminology('contractor') ?? 'Kontraktor';
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        if ($contractor->productionOrders()->exists()) {
            return back()->with('error', "{$contractorLabel} tidak dapat dihapus karena memiliki {$productionOrderLabel}.");
        }

        $contractor->delete();

        return redirect()->route('contractors.index')
            ->with('success', "{$contractorLabel} berhasil dihapus.");
    }
}
