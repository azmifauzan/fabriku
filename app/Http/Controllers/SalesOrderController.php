<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query()
            ->with(['customer']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by payment status
        if ($paymentStatus = $request->get('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // Filter by channel
        if ($channel = $request->get('channel')) {
            $query->where('channel', $channel);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('order_date', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('order_date', '<=', $endDate);
        }

        $orders = $query->latest('order_date')->paginate(15);

        return Inertia::render('SalesOrders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status', 'channel', 'start_date', 'end_date']),
            'stats' => [
                'total_orders' => SalesOrder::count(),
                'draft_orders' => SalesOrder::where('status', 'draft')->count(),
                'pending_orders' => SalesOrder::whereIn('status', ['confirmed', 'processing'])->count(),
                'completed_orders' => SalesOrder::where('status', 'completed')->count(),
                'pending_payment' => SalesOrder::where('payment_status', 'unpaid')->count(),
                'pending_payment' => SalesOrder::where('payment_status', 'unpaid')->count(),
                'realized_revenue' => SalesOrder::sum('paid_amount'),
                'outstanding_revenue' => SalesOrder::where('payment_status', '!=', 'paid')->sum(DB::raw('total_amount - paid_amount')),
            ],
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.inventoryItem.inventoryLocation', 'items.inventoryItem.productionOrder.preparationOrder.pattern']);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => $salesOrder,
        ]);
    }

    public function create()
    {
        return Inertia::render('SalesOrders/Create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'inventoryItems' => InventoryItem::with(['inventoryLocation', 'productionOrder.preparationOrder.pattern'])
                ->where('status', 'available')
                ->whereColumn('current_quantity', '>', 'reserved_quantity')
                ->get(['id', 'sku', 'production_order_id', 'location_id', 'current_quantity', 'reserved_quantity', 'selling_price', 'product_name', 'product_code']),
        ]);
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $items = [];

            foreach ($validated['items'] as $item) {
                $itemSubtotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $subtotal += $itemSubtotal;

                $items[] = [
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            if (isset($validated['discount_percentage']) && $validated['discount_percentage'] > 0) {
                $discountAmount = $subtotal * ($validated['discount_percentage'] / 100);
            }

            $taxAmount = $validated['tax_amount'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Create sales order
            $salesOrder = SalesOrder::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'channel' => $validated['channel'],
                'status' => $validated['status'] ?? 'draft',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'paid_amount' => $validated['paid_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'resi_number' => $validated['resi_number'] ?? null,
            ]);

            // Create items
            $salesOrder->items()->createMany($items);

            DB::commit();

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(SalesOrder $salesOrder)
    {
        if (! $salesOrder->canBeEdited()) {
            return back()->withErrors([
                'edit' => 'Sales order tidak dapat diedit dalam status '.$salesOrder->status,
            ]);
        }

        $salesOrder->load('items');

        return Inertia::render('SalesOrders/Edit', [
            'salesOrder' => $salesOrder,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'inventoryItems' => InventoryItem::with(['inventoryLocation', 'productionOrder.preparationOrder.pattern'])
                ->where('status', 'available')
                ->whereColumn('current_quantity', '>', 'reserved_quantity')
                ->get(['id', 'sku', 'production_order_id', 'location_id', 'current_quantity', 'reserved_quantity', 'selling_price', 'product_name', 'product_code']),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $items = [];

            foreach ($validated['items'] as $item) {
                $itemSubtotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $subtotal += $itemSubtotal;

                $items[] = [
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            if (isset($validated['discount_percentage']) && $validated['discount_percentage'] > 0) {
                $discountAmount = $subtotal * ($validated['discount_percentage'] / 100);
            }

            $taxAmount = $validated['tax_amount'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Update sales order
            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'channel' => $validated['channel'],
                'status' => $validated['status'] ?? $salesOrder->status,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'] ?? $salesOrder->payment_status,
                'paid_amount' => $validated['paid_amount'] ?? $salesOrder->paid_amount,
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'resi_number' => $validated['resi_number'] ?? null,
            ]);

            // Delete old items and create new ones
            $salesOrder->items()->delete();
            $salesOrder->items()->createMany($items);

            DB::commit();

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if (! $salesOrder->canBeCancelled()) {
            return back()->withErrors([
                'delete' => 'Sales order tidak dapat dihapus dalam status '.$salesOrder->status,
            ]);
        }

        $salesOrder->delete();

        return redirect()
            ->route('sales-orders.index')
            ->with('success', 'Sales order berhasil dihapus.');
    }

    public function print(SalesOrder $salesOrder)
    {
        // For now, return a simple view or just a placeholder message for testing
        // Ideally this would generate a PDF
        $salesOrder->load(['customer', 'items.inventoryItem']);
        $settings = \App\Models\SystemSetting::getAllForTenant(auth()->user()->tenant_id);

        return Inertia::render('SalesOrders/Print', [
            'salesOrder' => $salesOrder,
            'settings' => $settings,
        ]);
    }

    public function export(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.inventoryItem']);
        
        $csvFileName = 'Invoice-' . str_replace('/', '-', $salesOrder->invoice_number ?? $salesOrder->order_number) . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $callback = function() use($salesOrder) {
            $file = fopen('php://output', 'w');
            
            // Header Info
            fputcsv($file, ['INVOICE', $salesOrder->invoice_number ?? '-']);
            fputcsv($file, ['Order Number', $salesOrder->order_number]);
            fputcsv($file, ['Date', $salesOrder->order_date->format('Y-m-d')]);
            fputcsv($file, ['Customer', $salesOrder->customer->name]);
            fputcsv($file, ['Resi', $salesOrder->resi_number ?? '-']);
            fputcsv($file, []); // Empty line
            
            // Items Header
            fputcsv($file, ['Item', 'Quantity', 'Unit Price', 'Discount', 'Total']);
            
            // Items
            foreach ($salesOrder->items as $item) {
                fputcsv($file, [
                    $item->inventoryItem->product_name ?? $item->inventoryItem->sku,
                    $item->quantity,
                    $item->unit_price,
                    $item->discount_amount,
                    $item->subtotal
                ]);
            }
            
            fputcsv($file, []); // Empty line
            
            // Totals
            fputcsv($file, ['', '', '', 'Subtotal', $salesOrder->subtotal]);
            fputcsv($file, ['', '', '', 'Discount', $salesOrder->discount_amount]);
            fputcsv($file, ['', '', '', 'Tax', $salesOrder->tax_amount]);
            fputcsv($file, ['', '', '', 'Total', $salesOrder->total_amount]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
