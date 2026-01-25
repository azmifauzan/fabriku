<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\PreparationOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateMaterialStock extends Command
{
    protected $signature = 'material:recalculate-stock {--dry-run : Show what would be changed without updating}';

    protected $description = 'Recalculate material stock based on receipts and completed preparation orders';

    public function handle(): int
    {
        $this->info('Recalculating material stock...');

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all completed preparation orders
        $prepOrders = PreparationOrder::withoutGlobalScopes()
            ->where('status', 'completed')
            ->get();

        // Build material usage map from all completed prep orders
        $usageMap = [];
        foreach ($prepOrders as $order) {
            $usage = $order->material_usage ?? [];
            if (is_array($usage)) {
                foreach ($usage as $u) {
                    $materialId = $u['material_id'] ?? null;
                    $qty = $u['quantity'] ?? 0;
                    if ($materialId) {
                        $usageMap[$materialId] = ($usageMap[$materialId] ?? 0) + $qty;
                    }
                }
            }
        }

        // Get all materials
        $materials = Material::withoutGlobalScopes()->get();

        $this->table(
            ['Code', 'Name', 'Current Stock', 'Total Received', 'Total Used', 'Expected Stock', 'Status'],
            $materials->map(function ($material) use ($usageMap, $dryRun) {
                // Calculate total received from receipts
                $totalReceived = MaterialReceipt::withoutGlobalScopes()
                    ->where('material_id', $material->id)
                    ->sum('quantity');

                $totalUsed = $usageMap[$material->id] ?? 0;
                $expectedStock = max(0, $totalReceived - $totalUsed);

                $currentStock = $material->stock_quantity;
                $diff = abs($currentStock - $expectedStock);

                $status = $diff < 0.01 ? '✓' : '⚠️ MISMATCH';

                // Update if not dry run and there's a mismatch
                if (!$dryRun && $diff >= 0.01) {
                    $material->stock_quantity = $expectedStock;
                    $material->save();
                    $status = '✅ FIXED';
                }

                return [
                    $material->code,
                    substr($material->name, 0, 20),
                    number_format($currentStock, 2, ',', '.'),
                    number_format($totalReceived, 2, ',', '.'),
                    number_format($totalUsed, 2, ',', '.'),
                    number_format($expectedStock, 2, ',', '.'),
                    $status,
                ];
            })->toArray()
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('Run without --dry-run to apply fixes.');
        }

        return Command::SUCCESS;
    }
}
