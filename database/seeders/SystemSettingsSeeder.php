<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SystemSetting::set('bank_name', 'BCA');
        \App\Models\SystemSetting::set('account_number', '1234567890');
        \App\Models\SystemSetting::set('account_holder', 'Fabriku Admin');
        \App\Models\SystemSetting::set('membership_price_monthly', 25000, 'number');
        \App\Models\SystemSetting::set('membership_price_yearly', 250000, 'number');
    }
}
