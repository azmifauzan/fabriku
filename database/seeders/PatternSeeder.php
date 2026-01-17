<?php

namespace Database\Seeders;

use App\Models\Pattern;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PatternSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->first();

        if (! $tenant) {
            $this->command->warn('Demo tenant not found. Run TenantSeeder first.');

            return;
        }

        // Pattern 1: Mukena Dewasa
        Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'MKN-001',
            'name' => 'Mukena Dewasa Katun Rayon',
            'output_quantity' => 1,
            'description' => 'Mukena dewasa bahan katun rayon premium, nyaman digunakan',
            'estimated_labor_cost' => 15000,
            'instructions' => 'Jahit mukena dewasa sesuai pattern standar',
            'is_active' => true,
        ]);

        // Pattern 2: Daster Dewasa
        Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'DST-001',
            'name' => 'Daster Dewasa Wolfis',
            'output_quantity' => 1,
            'description' => 'Daster dewasa bahan wolfis, adem dan nyaman',
            'estimated_labor_cost' => 12000,
            'instructions' => 'Jahit daster dewasa dengan resleting depan',
            'is_active' => true,
        ]);

        // Pattern 3: Gamis
        Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'GMS-001',
            'name' => 'Gamis Casual',
            'output_quantity' => 1,
            'description' => 'Gamis casual untuk harian',
            'estimated_labor_cost' => 18000,
            'instructions' => 'Jahit gamis casual dengan detail sesuai pattern',
            'is_active' => true,
        ]);

        $this->command->info('Patterns seeded successfully!');
    }
}
