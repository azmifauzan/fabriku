<?php

namespace Database\Seeders;

use App\Models\Material;
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

        // Get materials
        $kainKatun = Material::where('tenant_id', $tenant->id)->where('code', 'KA-001')->first();
        $kainWolfis = Material::where('tenant_id', $tenant->id)->where('code', 'KA-002')->first();
        $benang = Material::where('tenant_id', $tenant->id)->where('code', 'BN-001')->first();
        $resleting = Material::where('tenant_id', $tenant->id)->where('code', 'AK-001')->first();

        // Pattern 1: Mukena Dewasa
        $mukena = Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'MKN-001',
            'name' => 'Mukena Dewasa Katun Rayon',
            'product_type' => 'mukena',
            'size' => 'all_size',
            'description' => 'Mukena dewasa bahan katun rayon premium, nyaman digunakan',
            'estimated_time' => 45,
            'standard_waste_percentage' => 5,
            'is_active' => true,
        ]);

        if ($kainKatun) {
            $mukena->materials()->attach($kainKatun->id, [
                'quantity_needed' => 2.5,
                'notes' => 'Untuk atasan dan bawahan',
            ]);
        }

        if ($benang) {
            $mukena->materials()->attach($benang->id, [
                'quantity_needed' => 0.1,
                'notes' => 'Benang jahit',
            ]);
        }

        // Pattern 2: Daster Dewasa
        $daster = Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'DST-001',
            'name' => 'Daster Dewasa Wolfis',
            'product_type' => 'daster',
            'size' => 'L',
            'description' => 'Daster dewasa bahan wolfis, adem dan nyaman',
            'estimated_time' => 30,
            'standard_waste_percentage' => 4,
            'is_active' => true,
        ]);

        if ($kainWolfis) {
            $daster->materials()->attach($kainWolfis->id, [
                'quantity_needed' => 2.0,
                'notes' => 'Bahan utama',
            ]);
        }

        if ($benang) {
            $daster->materials()->attach($benang->id, [
                'quantity_needed' => 0.08,
                'notes' => 'Benang jahit',
            ]);
        }

        if ($resleting) {
            $daster->materials()->attach($resleting->id, [
                'quantity_needed' => 1,
                'notes' => 'Resleting depan',
            ]);
        }

        $this->command->info('Patterns seeded successfully!');
    }
}
