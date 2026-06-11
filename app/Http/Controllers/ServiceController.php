<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\InventoryItem;
use App\Models\SalesOrderItem;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Service::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->get('status'));
        }

        $services = $query->latest()->paginate(self::DEFAULT_PER_PAGE);

        return Inertia::render('Services/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Services/Create', [
            'inventoryItems' => InventoryItem::query()
                ->where('status', 'available')
                ->orderBy('product_name')
                ->get(['id', 'sku', 'product_name']),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = Service::create($request->safe()->except(['consumables']));

        $this->syncConsumables($service, $request->validated('consumables') ?? []);

        return redirect()
            ->route('services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('Services/Edit', [
            'service' => $service->load('consumables.inventoryItem:id,sku,product_name'),
            'inventoryItems' => InventoryItem::query()
                ->where('status', 'available')
                ->orderBy('product_name')
                ->get(['id', 'sku', 'product_name']),
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->safe()->except(['consumables']));

        $this->syncConsumables($service, $request->validated('consumables') ?? []);

        return redirect()
            ->route('services.index')
            ->with('success', 'Layanan berhasil diupdate.');
    }

    /**
     * Replace consumable mapping (bahan pendukung yang auto-deduct saat layanan terjual).
     *
     * @param  array<int, array{inventory_item_id: int, quantity: int}>  $consumables
     */
    private function syncConsumables(Service $service, array $consumables): void
    {
        $service->consumables()->delete();

        foreach ($consumables as $row) {
            $service->consumables()->create([
                'tenant_id' => $service->tenant_id,
                'inventory_item_id' => $row['inventory_item_id'],
                'quantity' => $row['quantity'],
            ]);
        }
    }

    public function destroy(Service $service): RedirectResponse
    {
        $isUsed = SalesOrderItem::query()
            ->where('service_id', $service->id)
            ->exists();

        if ($isUsed) {
            return redirect()
                ->route('services.index')
                ->with('error', 'Layanan tidak bisa dihapus karena sudah dipakai di transaksi penjualan. Nonaktifkan layanan ini sebagai gantinya.');
        }

        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}
