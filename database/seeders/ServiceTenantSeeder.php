<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Service;
use App\Models\ServiceConsumable;
use App\Models\Staff;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Tenant $tenant): void
    {
        $location = InventoryLocation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'TEMPAT-USAHA'],
            ['name' => 'Bengkel Utama', 'is_active' => true]
        );

        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Sparepart'],
            ['description' => 'Suku cadang dan oli']
        );

        $adminUserId = User::where('tenant_id', $tenant->id)->where('role', 'admin')->value('id');

        // Sparepart inventory (produk fisik pendamping layanan)
        $spareparts = [
            ['sku' => 'SVC-OLI-001', 'name' => 'Oli Mesin 1L', 'qty' => 24, 'cost' => 38000, 'price' => 55000],
            ['sku' => 'SVC-BUSI-001', 'name' => 'Busi Standar', 'qty' => 30, 'cost' => 12000, 'price' => 20000],
            ['sku' => 'SVC-KMPS-001', 'name' => 'Kampas Rem Depan', 'qty' => 10, 'cost' => 35000, 'price' => 60000],
        ];

        $items = [];
        foreach ($spareparts as $part) {
            $item = InventoryItem::firstOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $part['sku']],
                [
                    'product_name' => $part['name'],
                    'source_type' => 'purchase',
                    'location_id' => $location->id,
                    'current_quantity' => $part['qty'],
                    'reserved_quantity' => 0,
                    'target_quantity' => $part['qty'],
                    'minimum_stock' => 5,
                    'unit_cost' => $part['cost'],
                    'selling_price' => $part['price'],
                    'status' => 'available',
                ]
            );
            $items[$part['sku']] = $item;

            StockAdjustment::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'inventory_item_id' => $item->id,
                    'adjustment_type' => StockAdjustment::TYPE_OPENING_BALANCE,
                ],
                [
                    'quantity_before' => 0,
                    'quantity_after' => $part['qty'],
                    'adjustment_quantity' => $part['qty'],
                    'reason' => 'Stock awal pembukaan',
                    'notes' => 'Saldo awal sparepart',
                    'unit_cost' => $part['cost'],
                    'adjusted_by' => $adminUserId,
                ]
            );
        }

        // Staff montir (untuk assignment per layanan)
        $staffList = [
            ['code' => 'MTR-001', 'name' => 'Budi Montir'],
            ['code' => 'MTR-002', 'name' => 'Andi Montir'],
        ];
        foreach ($staffList as $s) {
            Staff::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $s['code']],
                ['name' => $s['name'], 'is_active' => true]
            );
        }

        // Katalog layanan
        $services = [
            ['code' => 'SRV-001', 'name' => 'Jasa Cuci Motor Standar', 'category' => 'Cuci', 'price' => 20000, 'duration_minutes' => 30, 'description' => 'Cuci body dan semir ban'],
            ['code' => 'SRV-002', 'name' => 'Jasa Servis Ringan', 'category' => 'Servis', 'price' => 50000, 'duration_minutes' => 60, 'description' => 'Servis ringan karburator / injeksi'],
            ['code' => 'SRV-003', 'name' => 'Jasa Ganti Oli', 'category' => 'Servis', 'price' => 15000, 'duration_minutes' => 15, 'description' => 'Ongkos ganti oli mesin'],
        ];

        $createdServices = [];
        foreach ($services as $svc) {
            $createdServices[$svc['code']] = Service::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $svc['code']],
                $svc + ['is_active' => true]
            );
        }

        // Consumable mapping: Ganti Oli otomatis kurangi 1 botol oli
        if (isset($createdServices['SRV-003'], $items['SVC-OLI-001'])) {
            ServiceConsumable::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'service_id' => $createdServices['SRV-003']->id,
                    'inventory_item_id' => $items['SVC-OLI-001']->id,
                ],
                ['quantity' => 1]
            );
        }
    }
}
