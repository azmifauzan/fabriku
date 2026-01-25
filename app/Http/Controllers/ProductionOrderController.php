<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionOrderRequest;
use App\Http\Requests\UpdateProductionOrderRequest;
use App\Models\Contractor;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Services\ProductionService;
use Inertia\Inertia;

class ProductionOrderController extends Controller
{
    public function index()
    {
        $orders = ProductionOrder::query()
            ->with(['preparationOrder.pattern', 'contractor'])
            ->when(request('search'), function ($query, $search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('contractor', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('contractor_id'), fn ($query, $id) => $query->where('contractor_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $contractors = Contractor::active()->get(['id', 'name']);

        return Inertia::render('ProductionOrders/Index', [
            'orders' => $orders,
            'contractors' => $contractors,
            'filters' => request()->only(['search', 'status', 'type', 'contractor_id']),
            'stats' => [
                'total_orders' => ProductionOrder::count(),
                'draft_orders' => ProductionOrder::where('status', 'draft')->count(),
                'in_progress_orders' => ProductionOrder::whereIn('status', ['in_progress', 'sent'])->count(),
                'completed_orders' => ProductionOrder::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function create()
    {
        $preparationOrders = PreparationOrder::query()
            ->with(['pattern'])
            ->where('status', 'completed')
            ->whereDoesntHave('productionOrders')
            ->latest()
            ->get();

        $contractors = Contractor::active()->get(['id', 'name', 'type', 'specialty']);

        return Inertia::render('ProductionOrders/Form', [
            'preparationOrders' => $preparationOrders,
            'contractors' => $contractors,
        ]);
    }

    public function store(StoreProductionOrderRequest $request)
    {
        ProductionOrder::create(array_merge($request->validated(), [
            'status' => 'draft',
        ]));

        $tenant = auth()->user()?->tenant;
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        return redirect()->route('production-orders.index')
            ->with('success', "{$productionOrderLabel} berhasil ditambahkan.");
    }

    public function show(ProductionOrder $productionOrder)
    {
        $productionOrder->load([
            'preparationOrder.pattern',
            'contractor',
        ]);

        return Inertia::render('ProductionOrders/Show', [
            'order' => $productionOrder,
        ]);
    }

    public function edit(ProductionOrder $productionOrder)
    {
        if (! $productionOrder->canBeEdited()) {
            $tenant = auth()->user()?->tenant;
            $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

            return back()->with('error', "{$productionOrderLabel} tidak dapat diedit karena statusnya sudah completed atau cancelled.");
        }

        $preparationOrders = PreparationOrder::query()
            ->with(['pattern'])
            ->where('status', 'completed')
            ->where(function ($query) use ($productionOrder) {
                $query->whereDoesntHave('productionOrders')
                    ->orWhereHas('productionOrders', fn ($q) => $q->whereKey($productionOrder->id));
            })
            ->latest()
            ->get();

        $contractors = Contractor::active()->get(['id', 'name', 'type', 'specialty', 'rate_per_piece', 'rate_per_hour']);

        return Inertia::render('ProductionOrders/Form', [
            'productionOrder' => $productionOrder,
            'preparationOrders' => $preparationOrders,
            'contractors' => $contractors,
        ]);
    }

    public function update(UpdateProductionOrderRequest $request, ProductionOrder $productionOrder)
    {
        if (! $productionOrder->canBeEdited()) {
            $tenant = auth()->user()?->tenant;
            $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

            return back()->with('error', "{$productionOrderLabel} tidak dapat diedit karena statusnya sudah completed atau cancelled.");
        }

        $productionOrder->update($request->validated());

        $tenant = auth()->user()?->tenant;
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        return redirect()->route('production-orders.index')
            ->with('success', "{$productionOrderLabel} berhasil diperbarui.");
    }

    public function destroy(ProductionOrder $productionOrder)
    {
        if (! $productionOrder->canBeDeleted()) {
            $tenant = auth()->user()?->tenant;
            $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

            return back()->with('error', "{$productionOrderLabel} tidak dapat dihapus karena statusnya bukan draft atau sudah memiliki batch.");
        }

        $productionOrder->delete();

        $tenant = auth()->user()?->tenant;
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        return redirect()->route('production-orders.index')
            ->with('success', "{$productionOrderLabel} berhasil dihapus.");
    }

    public function send(ProductionOrder $productionOrder, ProductionService $productionService)
    {
        $productionService->send($productionOrder);

        $tenant = auth()->user()?->tenant;
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        return back()->with('success', "{$productionOrderLabel} berhasil dikirim.");
    }

    public function start(ProductionOrder $productionOrder)
    {
        $productionOrder->update([
            'status' => 'in_progress',
        ]);

        $tenant = auth()->user()?->tenant;
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        return back()->with('success', "{$productionOrderLabel} berhasil dimulai produksinya.");
    }

    public function markComplete(ProductionOrder $productionOrder)
    {
        $productionOrder->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        $tenant = auth()->user()?->tenant;
        $productionOrderLabel = $tenant?->getTerminology('production_order') ?? 'Production order';

        return back()->with('success', "{$productionOrderLabel} berhasil ditandai complete.");
    }
}
