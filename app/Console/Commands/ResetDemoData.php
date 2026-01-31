<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset {--tenant= : Specific tenant ID or slug to reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset demo data to initial state for demo tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting demo data reset...');

        // Demo tenant emails
        $demoEmails = [
            'admin@konveksi.com',
            'admin@kuemama.com',
            'admin@crafty.com',
            'admin@glowbeauty.com',
        ];

        $tenantOption = $this->option('tenant');

        if ($tenantOption) {
            // Reset specific tenant
            $tenant = is_numeric($tenantOption)
                ? Tenant::find($tenantOption)
                : Tenant::where('slug', $tenantOption)->first();

            if (! $tenant) {
                $this->error("Tenant not found: {$tenantOption}");

                return 1;
            }

            $this->resetTenant($tenant);
        } else {
            // Reset all demo tenants
            $demoTenants = Tenant::whereHas('users', function ($query) use ($demoEmails) {
                $query->whereIn('email', $demoEmails);
            })->get();

            if ($demoTenants->isEmpty()) {
                $this->warn('No demo tenants found.');

                return 0;
            }

            $this->info("Found {$demoTenants->count()} demo tenant(s) to reset.");

            foreach ($demoTenants as $tenant) {
                $this->resetTenant($tenant);
            }
        }

        $this->info('Demo data reset completed!');

        return 0;
    }

    private function resetTenant(Tenant $tenant)
    {
        $this->info("Resetting tenant: {$tenant->name} (ID: {$tenant->id})");

        DB::beginTransaction();
        try {
            // Delete all data related to this tenant
            $this->info('  - Clearing sales orders...');
            $tenant->users()->first()?->tenant->salesOrders()->delete();

            $this->info('  - Clearing production orders...');
            DB::table('production_orders')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing preparation orders...');
            DB::table('preparation_orders')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing inventory...');
            DB::table('inventory_items')->where('tenant_id', $tenant->id)->delete();
            DB::table('stock_adjustments')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing materials...');
            DB::table('material_receipts')->where('tenant_id', $tenant->id)->delete();
            DB::table('preparation_material_usage')->whereIn('preparation_order_id', function ($query) use ($tenant) {
                $query->select('id')->from('preparation_orders')->where('tenant_id', $tenant->id);
            })->delete();
            DB::table('materials')->where('tenant_id', $tenant->id)->delete();
            DB::table('material_types')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing patterns...');
            DB::table('patterns')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing contractors...');
            DB::table('contractors')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing customers...');
            DB::table('customers')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing staff...');
            DB::table('staff')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing inventory locations...');
            DB::table('inventory_locations')->where('tenant_id', $tenant->id)->delete();

            // Reset subscription to trial with fresh 30 days
            $tenant->update([
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]);

            DB::commit();
            $this->info("  ✓ Tenant {$tenant->name} reset successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("  ✗ Failed to reset tenant {$tenant->name}: {$e->getMessage()}");
        }
    }
}
