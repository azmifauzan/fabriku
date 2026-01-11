<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = \App\Models\Tenant::first();

        // Create 10 customers
        $customers = \App\Models\Customer::factory(10)
            ->create(['tenant_id' => $tenant->id]);

        // Get existing inventory items
        $inventoryItems = \App\Models\InventoryItem::where('tenant_id', $tenant->id)
            ->where('status', 'available')
            ->get();

        if ($inventoryItems->isEmpty()) {
            $this->command->info('No inventory items found. Skipping sales orders creation.');

            return;
        }

        // Create 15 sales orders with items
        foreach ($customers->random(5) as $customer) {
            // Create 1-3 orders per customer
            $orderCount = rand(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $items = $inventoryItems->random(rand(1, 3));

                $subtotal = 0;
                $orderItems = [];

                foreach ($items as $item) {
                    $quantity = rand(1, 5);
                    $unitPrice = (float) $item->selling_price;
                    $discountAmount = 0;
                    $itemSubtotal = ($quantity * $unitPrice) - $discountAmount;

                    $orderItems[] = [
                        'inventory_item_id' => $item->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_amount' => $discountAmount,
                        'subtotal' => $itemSubtotal,
                    ];

                    $subtotal += $itemSubtotal;
                }

                $discountPercentage = $customer->discount_percentage;
                $discountAmount = $subtotal * ($discountPercentage / 100);
                $taxAmount = 0;
                $totalAmount = $subtotal - $discountAmount + $taxAmount;

                $status = collect(['draft', 'confirmed', 'processing', 'completed'])->random();
                $paymentStatus = $status === 'completed' ? 'paid' : collect(['unpaid', 'partial'])->random();
                $paidAmount = $paymentStatus === 'paid' ? $totalAmount : ($paymentStatus === 'partial' ? $totalAmount / 2 : 0);

                $order = \App\Models\SalesOrder::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'order_date' => now()->subDays(rand(0, 90)),
                    'channel' => collect(['offline', 'online', 'reseller'])->random(),
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'discount_percentage' => $discountPercentage,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'payment_method' => collect(['cash', 'transfer', 'qris'])->random(),
                    'payment_status' => $paymentStatus,
                    'paid_amount' => $paidAmount,
                    'completed_date' => $status === 'completed' ? now()->subDays(rand(1, 30)) : null,
                    'notes' => collect([null, 'Pesanan urgent', 'Kirim via ekspedisi'])->random(),
                ]);

                $order->items()->createMany($orderItems);
            }
        }

        $this->command->info('✅ Sales seeder completed: 10 customers, ~15 sales orders');
    }
}
