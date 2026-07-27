<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Service;
use App\Models\ServiceConsumable;
use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
            if ($paymentStatus === 'overdue') {
                $query->whereIn('payment_status', ['unpaid', 'partial'])
                    ->where('status', '!=', 'cancelled')
                    ->whereNotNull('payment_due_date')
                    ->whereDate('payment_due_date', '<', now());
            } else {
                $query->where('payment_status', $paymentStatus);
            }
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

        $orders = $query->latest('order_date')->paginate(self::DEFAULT_PER_PAGE);

        return Inertia::render('SalesOrders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status', 'channel', 'start_date', 'end_date']),
            'stats' => [
                'total_orders' => SalesOrder::count(),
                'draft_orders' => SalesOrder::where('status', 'draft')->count(),
                'pending_orders' => SalesOrder::whereIn('status', ['confirmed', 'processing'])->count(),
                'completed_orders' => SalesOrder::where('status', 'completed')->count(),
                'pending_payment' => SalesOrder::where('payment_status', 'unpaid')
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'realized_revenue' => SalesOrder::sum('paid_amount'),
                'outstanding_revenue' => SalesOrder::where('payment_status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->sum(DB::raw('total_amount - paid_amount')),
            ],
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.inventoryItem.inventoryLocation', 'items.inventoryItem.productionOrder.preparationOrder.pattern', 'items.service', 'items.servedBy', 'payments']);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => $salesOrder,
            'allowedTransitions' => $salesOrder->allowedTransitions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('SalesOrders/Create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'inventoryItems' => InventoryItem::with(['inventoryLocation', 'productionOrder.preparationOrder.pattern'])
                ->where('status', 'available')
                ->whereColumn('current_quantity', '>', 'reserved_quantity')
                ->get(['id', 'sku', 'production_order_id', 'location_id', 'current_quantity', 'reserved_quantity', 'selling_price', 'product_name', 'product_code', 'image_path']),
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
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
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
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCost;

            // Create sales order
            $salesOrder = SalesOrder::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'channel' => $validated['channel'],
                'status' => 'draft', // Force status to draft on creation
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'paid_amount' => $validated['paid_amount'] ?? 0,
                'payment_due_date' => $validated['payment_due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'resi_number' => $validated['resi_number'] ?? null,
            ]);

            // Create items
            $salesOrder->items()->createMany($items);

            // Stock reservation is now handled by SalesOrderObserver
            // foreach ($validated['items'] as $item) {
            //     $inventoryItem = InventoryItem::lockForUpdate()->find($item['inventory_item_id']);
            //     $inventoryItem?->reserveStock((int) $item['quantity']);
            // }

            DB::commit();

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order berhasil dibuat.');
        } catch (UniqueConstraintViolationException $e) {
            DB::rollBack();

            return back()->withErrors([
                'order_number' => 'Nomor order sudah digunakan. Silakan coba lagi.',
            ])->withInput();
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
                ->get(['id', 'sku', 'production_order_id', 'location_id', 'current_quantity', 'reserved_quantity', 'selling_price', 'product_name', 'product_code', 'image_path']),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder)
    {
        if (! $salesOrder->canBeEdited()) {
            abort(403, 'Sales order tidak dapat diedit dalam status '.$salesOrder->status);
        }

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
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
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
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCost;

            // Replace items FIRST, while the order still has its old status, and
            // delete per-model so SalesOrderItemObserver fires (bulk delete skips
            // events and would leak reservations on confirmed orders). The status
            // change below then lets SalesOrderObserver handle stock exactly once
            // over the final item set.
            $salesOrder->items()->get()->each->delete();
            $salesOrder->items()->createMany($items);

            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'channel' => $validated['channel'],
                'status' => $validated['status'] ?? $salesOrder->status,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'] ?? $salesOrder->payment_status,
                'paid_amount' => $validated['paid_amount'] ?? $salesOrder->paid_amount,
                'payment_due_date' => $validated['payment_due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'resi_number' => $validated['resi_number'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatus(UpdateStatusRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        $newStatus = $request->validated('status');

        if (! $salesOrder->canTransitionTo($newStatus)) {
            abort(422, "Transisi status dari '{$salesOrder->status}' ke '{$newStatus}' tidak diizinkan.");
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'shipped') {
            $updateData['resi_number'] = $request->validated('resi_number');
            $updateData['shipped_date'] = now();
        }

        if ($newStatus === 'completed') {
            $updateData['completed_date'] = now();
        }

        // When cancelling, fix payment_status so the order is excluded from
        // piutang/receivables queries. Previously only refunded orders got
        // their payment_status updated — unpaid cancelled orders stayed
        // 'unpaid' and kept appearing in outstanding-receivable totals.
        if ($newStatus === 'cancelled') {
            if ($salesOrder->paid_amount > 0) {
                $updateData['paid_amount'] = 0;
                $updateData['payment_status'] = 'refunded';
            } else {
                $updateData['payment_status'] = 'cancelled';
            }
        }

        // Capture paid_amount before update zeros it out
        $refundAmount = $salesOrder->paid_amount;

        DB::beginTransaction();
        try {
            $salesOrder->update($updateData);

            // Record refund payment if the cancelled order had prior payments
            if ($newStatus === 'cancelled' && $refundAmount > 0) {
                $salesOrder->payments()->create([
                    'tenant_id' => $salesOrder->tenant_id,
                    'amount' => -$refundAmount,
                    'method' => 'refund',
                    'paid_at' => now(),
                    'note' => 'Auto refund for cancelled order',
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $label = match ($newStatus) {
            'confirmed' => 'Pesanan dikonfirmasi.',
            'processing' => 'Pesanan sedang diproses.',
            'shipped' => 'Pesanan telah dikirim.',
            'completed' => 'Pesanan selesai.',
            'cancelled' => 'Pesanan dibatalkan.',
            default => 'Status berhasil diupdate.',
        };

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', $label);
    }

    public function updatePayment(StorePaymentRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        if ($salesOrder->status === 'cancelled') {
            abort(403, 'Pembayaran sales order yang dibatalkan tidak dapat ditambah.');
        }

        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $salesOrder->payments()->create([
                'tenant_id' => $salesOrder->tenant_id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'paid_at' => $validated['paid_at'],
                'note' => $validated['note'] ?? null,
            ]);

            $totalPaid = $salesOrder->payments()->sum('amount');

            $paymentStatus = match (true) {
                $totalPaid <= 0 => 'unpaid',
                $totalPaid >= $salesOrder->total_amount => 'paid',
                default => 'partial',
            };

            $salesOrder->update([
                'paid_amount' => $totalPaid,
                'payment_status' => $paymentStatus,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if (! $salesOrder->canBeCancelled()) {
            return back()->withErrors([
                'delete' => 'Sales order tidak dapat dihapus dalam status '.$salesOrder->status,
            ]);
        }

        DB::beginTransaction();
        try {
            $salesOrder->delete(); // Observer handles stock release

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('sales-orders.index')
            ->with('success', 'Sales order berhasil dihapus.');
    }

    public function quickCheckout(): Response
    {
        $items = InventoryItem::query()
            ->where('status', 'available')
            ->whereColumn('current_quantity', '>', 'reserved_quantity')
            ->select('id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'selling_price', 'unit_cost', 'image_path')
            ->orderBy('product_name')
            ->get();

        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'price', 'category']);

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Staff untuk assignment layanan (kategori service)
        $staff = Staff::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('SalesOrders/QuickCheckout', [
            'inventoryItems' => $items,
            'services' => $services,
            'customers' => $customers,
            'staff' => $staff,
        ]);
    }

    public function quickCheckoutStore(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'payment_method' => ['required', 'string'],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', 'required_without:items.*.service_id', 'integer', Rule::exists('inventory_items', 'id')->where('tenant_id', $tenantId)],
            'items.*.service_id' => ['nullable', 'required_without:items.*.inventory_item_id', 'integer', Rule::exists('services', 'id')->where('tenant_id', $tenantId)],
            'items.*.served_by' => ['nullable', 'integer', Rule::exists('staff', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $consumableNeeds = [];

            foreach ($request->input('items', []) as $index => $line) {
                if (! empty($line['inventory_item_id']) && ! empty($line['service_id'])) {
                    $validator->errors()->add("items.{$index}.service_id", 'Satu baris hanya boleh berisi produk atau layanan, tidak keduanya.');
                }

                // Akumulasi kebutuhan consumable per inventory item untuk validasi stok
                if (! empty($line['service_id']) && ! empty($line['quantity'])) {
                    $consumables = ServiceConsumable::query()
                        ->where('service_id', $line['service_id'])
                        ->get();

                    foreach ($consumables as $consumable) {
                        $consumableNeeds[$consumable->inventory_item_id] =
                            ($consumableNeeds[$consumable->inventory_item_id] ?? 0)
                            + ($consumable->quantity * (int) $line['quantity']);
                    }
                }
            }

            foreach ($consumableNeeds as $inventoryItemId => $needed) {
                $item = InventoryItem::find($inventoryItemId);

                if ($item && $item->available_stock < $needed) {
                    $validator->errors()->add(
                        'items',
                        "Stok bahan pendukung \"{$item->product_name}\" tidak cukup (butuh {$needed}, tersedia {$item->available_stock})."
                    );
                }
            }
        });

        $validated = $validator->validate();

        if (! empty($validated['customer_id'])) {
            $customer = Customer::findOrFail($validated['customer_id']);
        } else {
            $customer = Customer::firstOrCreate(
                ['tenant_id' => $tenantId, 'code' => 'WALK-IN'],
                [
                    'name' => 'Walk-in Customer',
                    'is_active' => true,
                ]
            );
        }

        DB::transaction(function () use ($validated, $customer, $tenantId) {
            $subtotal = 0;
            $items = [];

            foreach ($validated['items'] as $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $subtotal += $lineSubtotal;
                $items[] = [
                    'inventory_item_id' => $line['inventory_item_id'] ?? null,
                    'service_id' => $line['service_id'] ?? null,
                    'served_by' => $line['served_by'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => 0,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $salesOrder = SalesOrder::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'order_date' => now(),
                'channel' => 'offline',
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'discount_percentage' => 0,
                'tax_amount' => 0,
                'total_amount' => $subtotal,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'paid_amount' => $subtotal,
                'completed_date' => now(),
            ]);

            $salesOrder->items()->createMany($items);

            // Deduct stock directly — observer only handles updates, not creates
            foreach ($validated['items'] as $line) {
                if (! empty($line['inventory_item_id'])) {
                    $inventoryItem = InventoryItem::lockForUpdate()->find($line['inventory_item_id']);
                    $inventoryItem?->deductStock((int) $line['quantity']);
                }

                // Auto-deduct consumables yang ter-mapping ke layanan
                if (! empty($line['service_id'])) {
                    $consumables = ServiceConsumable::query()
                        ->where('service_id', $line['service_id'])
                        ->get();

                    foreach ($consumables as $consumable) {
                        $item = InventoryItem::lockForUpdate()->find($consumable->inventory_item_id);
                        $item?->deductStock($consumable->quantity * (int) $line['quantity']);
                    }
                }
            }
        });

        return redirect()->route('sales-orders.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function invoice(SalesOrder $salesOrder)
    {
        // For now, return a simple view or just a placeholder message for testing
        // Ideally this would generate a PDF
        $salesOrder->load(['customer', 'items.inventoryItem', 'items.service', 'items.servedBy']);
        $settings = SystemSetting::getAllForTenant(auth()->user()->tenant_id);

        return Inertia::render('SalesOrders/Invoice', [
            'salesOrder' => $salesOrder,
            'settings' => $settings,
        ]);
    }

    public function deliveryOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.inventoryItem', 'items.service', 'items.servedBy']);
        $settings = SystemSetting::getAllForTenant(auth()->user()->tenant_id);

        return Inertia::render('SalesOrders/DeliveryOrder', [
            'salesOrder' => $salesOrder,
            'settings' => $settings,
        ]);
    }

    public function export(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.inventoryItem', 'items.service', 'items.servedBy']);

        $csvFileName = 'Invoice-'.str_replace('/', '-', $salesOrder->invoice_number ?? $salesOrder->order_number).'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$csvFileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($salesOrder) {
            $file = fopen('php://output', 'w');

            // Header Info
            fputcsv($file, ['INVOICE', $salesOrder->invoice_number ?? '-']);
            fputcsv($file, ['Order Number', $salesOrder->order_number]);
            fputcsv($file, ['Date', $salesOrder->order_date->format('Y-m-d')]);
            fputcsv($file, ['Due Date', $salesOrder->payment_due_date ? $salesOrder->payment_due_date->format('Y-m-d') : '-']);
            fputcsv($file, ['Customer', $salesOrder->customer->name]);
            fputcsv($file, ['Resi', $salesOrder->resi_number ?? '-']);
            fputcsv($file, []); // Empty line

            // Items Header
            fputcsv($file, ['Item', 'Quantity', 'Unit Price', 'Discount', 'Total']);

            // Items
            foreach ($salesOrder->items as $item) {
                $itemName = $item->inventoryItem ? ($item->inventoryItem->product_name ?? $item->inventoryItem->sku) : ($item->service->name ?? 'Layanan');
                fputcsv($file, [
                    $itemName,
                    $item->quantity,
                    $item->unit_price,
                    $item->discount_amount,
                    $item->subtotal,
                ]);
            }

            fputcsv($file, []); // Empty line

            // Totals
            fputcsv($file, ['', '', '', 'Subtotal', $salesOrder->subtotal]);
            fputcsv($file, ['', '', '', 'Discount', $salesOrder->discount_amount]);
            fputcsv($file, ['', '', '', 'Tax', $salesOrder->tax_amount]);
            fputcsv($file, ['', '', '', 'Shipping Cost', $salesOrder->shipping_cost]);
            fputcsv($file, ['', '', '', 'Total', $salesOrder->total_amount]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
