<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreparationOrderRequest;
use App\Http\Requests\UpdatePreparationOrderRequest;
use App\Models\Material;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\Staff;
use App\Services\MaterialStockService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreparationOrderController extends Controller
{
    public function __construct(
        protected MaterialStockService $stockService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $orders = PreparationOrder::query()
            ->with(['pattern', 'preparedBy'])
            ->when($request->search, fn ($q) => $q->where('order_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->pattern_id, fn ($q) => $q->where('pattern_id', $request->pattern_id))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('PreparationOrders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'pattern_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $patterns = Pattern::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category', 'product_type']);

        $materials = Material::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'unit', 'current_stock']);

        $staff = Staff::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'position']);

        return Inertia::render('PreparationOrders/Form', [
            'patterns' => $patterns,
            'materials' => $materials,
            'staff' => $staff,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePreparationOrderRequest $request)
    {
        // Check stock availability
        $insufficient = $this->stockService->checkStockAvailability($request->materials_used);

        if (! empty($insufficient)) {
            return back()->withErrors([
                'materials_used' => 'Stock tidak mencukupi untuk beberapa material: '.
                    implode(', ', array_column($insufficient, 'material_name')),
            ])->withInput();
        }

        $order = PreparationOrder::create($request->validated());

        return redirect()
            ->route('preparation-orders.index')
            ->with('success', 'Preparation order berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(PreparationOrder $preparationOrder): Response
    {
        $preparationOrder->load(['pattern', 'preparedBy', 'productionOrders']);

        return Inertia::render('PreparationOrders/Show', [
            'order' => [
                ...$preparationOrder->toArray(),
                'prepared_by_staff' => $preparationOrder->preparedBy,
                'can_be_edited' => $preparationOrder->canBeEdited(),
                'can_be_deleted' => $preparationOrder->canBeDeleted(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PreparationOrder $preparationOrder): Response
    {
        if (! $preparationOrder->canBeEdited()) {
            abort(403, 'Order cannot be edited');
        }

        $patterns = Pattern::where('is_active', true)->get(['id', 'code', 'name', 'category']);
        $materials = Material::where('is_active', true)->get(['id', 'code', 'name', 'unit', 'current_stock']);
        $staff = Staff::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'position']);

        return Inertia::render('PreparationOrders/Form', [
            'order' => $preparationOrder,
            'patterns' => $patterns,
            'materials' => $materials,
            'staff' => $staff,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePreparationOrderRequest $request, PreparationOrder $preparationOrder)
    {
        if (! $preparationOrder->canBeEdited()) {
            abort(403, 'Order cannot be edited');
        }

        // Check stock if materials changed
        if ($request->has('materials_used')) {
            $insufficient = $this->stockService->checkStockAvailability($request->materials_used);

            if (! empty($insufficient)) {
                return back()->withErrors([
                    'materials_used' => 'Stock tidak mencukupi',
                ])->withInput();
            }
        }

        $preparationOrder->update($request->validated());

        return redirect()
            ->route('preparation-orders.index')
            ->with('success', 'Preparation order berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PreparationOrder $preparationOrder)
    {
        if (! $preparationOrder->canBeDeleted()) {
            abort(403, 'Order cannot be deleted');
        }

        $preparationOrder->delete();

        return redirect()
            ->route('preparation-orders.index')
            ->with('success', 'Preparation order berhasil dihapus');
    }
}
