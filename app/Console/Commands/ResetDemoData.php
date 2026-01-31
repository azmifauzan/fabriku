<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset {--tenant= : Specific tenant ID or slug to reset} {--no-reseed : Skip reseeding data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset demo data to initial state for demo tenants and reseed with default data';

    /**
     * Execute the command.
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
        $noReseed = $this->option('no-reseed');

        if ($tenantOption) {
            // Reset specific tenant
            $tenant = is_numeric($tenantOption)
                ? Tenant::find($tenantOption)
                : Tenant::where('name', 'like', "%{$tenantOption}%")->first();

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

        if (! $noReseed) {
            $this->newLine();
            $this->info('🌱 Reseeding demo data...');
            Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
            $this->info('✅ Demo data reseeded successfully!');
        }

        $this->newLine();
        $this->info('✅ Demo data reset completed!');

        return 0;
    }

    private function resetTenant(Tenant $tenant)
    {
        $this->info("Resetting tenant: {$tenant->name} (ID: {$tenant->id})");

        DB::beginTransaction();
        try {
            // Delete in correct order to avoid FK constraints
            $this->info('  - Clearing sales order items...');
            DB::table('sales_order_items')->whereIn('sales_order_id', function ($query) use ($tenant) {
                $query->select('id')->from('sales_orders')->where('tenant_id', $tenant->id);
            })->delete();

            $this->info('  - Clearing sales orders...');
            DB::table('sales_orders')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing inventory...');
            DB::table('inventory_items')->where('tenant_id', $tenant->id)->delete();
            DB::table('stock_adjustments')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing production orders...');
            DB::table('production_orders')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing preparation material usages...');
            DB::table('preparation_material_usages')->whereIn('preparation_order_id', function ($query) use ($tenant) {
                $query->select('id')->from('preparation_orders')->where('tenant_id', $tenant->id);
            })->delete();

            $this->info('  - Clearing preparation orders...');
            DB::table('preparation_orders')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing patterns...');
            DB::table('patterns')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing material receipts...');
            DB::table('material_receipts')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing material attributes...');
            DB::table('material_attributes')->whereIn('material_id', function ($query) use ($tenant) {
                $query->select('id')->from('materials')->where('tenant_id', $tenant->id);
            })->delete();

            $this->info('  - Clearing materials...');
            DB::table('materials')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing material types...');
            DB::table('material_types')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing contractors...');
            DB::table('contractors')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing customers...');
            DB::table('customers')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing staff...');
            DB::table('staff')->where('tenant_id', $tenant->id)->delete();

            $this->info('  - Clearing inventory locations...');
            DB::table('inventory_locations')->where('tenant_id', $tenant->id)->delete();

            // Keep users but reset subscription to trial with fresh 30 days
            $tenant->update([
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'trial_reminder_7days_sent_at' => null,
                'trial_reminder_3days_sent_at' => null,
                'trial_reminder_1day_sent_at' => null,
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
