<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::set('bank_name', 'BCA');
        SystemSetting::set('account_number', '1234567890');
        SystemSetting::set('account_holder', 'Fabriku Admin');
        SystemSetting::set('membership_price_monthly', 25000, 'number');
        SystemSetting::set('membership_price_yearly', 250000, 'number');
    }
}
