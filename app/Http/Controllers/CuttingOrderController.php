<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCuttingOrderRequest;
use App\Http\Requests\UpdateCuttingOrderRequest;
use App\Models\CuttingOrder;
use App\Models\Material;
use App\Models\Pattern;
use Inertia\Inertia;

class CuttingOrderController extends Controller
{
    public function index()
    {
        $orders = CuttingOrder::query()
            ->with(['pattern:id,code,name,product_type', 'cutter:id,name'])
            ->withCount('results')
            ->when(request('search'), function ($query, $search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('pattern', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('CuttingOrders/Index', [
            'cuttingOrders' => $orders,
            'filters' => request()->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $patterns = Pattern::query()
            ->with(['materials.material'])
            ->where('is_active', true)
            ->select('id', 'code', 'name', 'product_type', 'size', 'estimated_cost')
            ->orderBy('name')
            ->get();

        $materials = Material::query()
            ->where('is_active', true)
            ->select('id', 'code', 'name', 'unit', 'current_stock')
            ->orderBy('name')
            ->get();

        return Inertia::render('CuttingOrders/CuttingOrderForm', [
            'patterns' => $patterns,
            'materials' => $materials,
            'isEdit' => false,
        ]);
    }

    public function store(StoreCuttingOrderRequest $request)
    {
        $order = CuttingOrder::create($request->validated());

        return redirect()->route('cutting-orders.index')
            ->with('success', 'Cutting order berhasil dibuat.');
    }

    public function edit(CuttingOrder $cuttingOrder)
    {
        if (! $cuttingOrder->canBeEdited()) {
            return back()->with('error', 'Order dengan status '.$cuttingOrder->status.' tidak bisa diedit.');
        }

        $cuttingOrder->load('pattern.materials.material');

        $patterns = Pattern::query()
            ->with(['materials.material'])
            ->where('is_active', true)
            ->select('id', 'code', 'name', 'product_type', 'size', 'estimated_cost')
            ->orderBy('name')
            ->get();

        $materials = Material::query()
            ->where('is_active', true)
            ->select('id', 'code', 'name', 'unit', 'current_stock')
            ->orderBy('name')
            ->get();

        return Inertia::render('CuttingOrders/CuttingOrderForm', [
            'cuttingOrder' => $cuttingOrder,
            'patterns' => $patterns,
            'materials' => $materials,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateCuttingOrderRequest $request, CuttingOrder $cuttingOrder)
    {
        if (! $cuttingOrder->canBeEdited()) {
            return back()->withErrors(['order' => 'Order dengan status '.$cuttingOrder->status.' tidak bisa diedit.']);
        }

        $cuttingOrder->update($request->validated());

        return redirect()->route('cutting-orders.index')
            ->with('success', 'Cutting order berhasil diupdate.');
    }

    public function destroy(CuttingOrder $cuttingOrder)
    {
        if (! $cuttingOrder->canBeDeleted()) {
            return back()->withErrors(['order' => 'Order dengan status '.$cuttingOrder->status.' tidak bisa dihapus.']);
        }

        $cuttingOrder->delete();

        return redirect()->route('cutting-orders.index')
            ->with('success', 'Cutting order berhasil dihapus.');
    }
}
