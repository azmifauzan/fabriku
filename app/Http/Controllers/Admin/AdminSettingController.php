<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::getAllForTenant(null);

        // Ensure bank_accounts is an array
        if (! isset($settings['bank_accounts']) || ! is_array($settings['bank_accounts'])) {
            $settings['bank_accounts'] = [];
        }

        // Ensure max_staff_per_tenant has a default
        if (! isset($settings['max_staff_per_tenant'])) {
            $settings['max_staff_per_tenant'] = 5;
        }

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'bank_accounts' => 'required|array|min:1',
            'bank_accounts.*.bank_name' => 'required|string|max:100',
            'bank_accounts.*.account_number' => 'required|string|max:50',
            'bank_accounts.*.account_holder' => 'required|string|max:100',
            'membership_price_monthly' => 'required|numeric|min:0',
            'membership_price_yearly' => 'required|numeric|min:0',
            'membership_features' => 'nullable|array',
            'max_staff_per_tenant' => 'required|integer|min:1|max:100',
        ]);

        SystemSetting::set('bank_accounts', $request->bank_accounts, 'json');
        SystemSetting::set('membership_price_monthly', $request->membership_price_monthly, 'number');
        SystemSetting::set('membership_price_yearly', $request->membership_price_yearly, 'number');
        SystemSetting::set('max_staff_per_tenant', $request->max_staff_per_tenant, 'number');

        if ($request->has('membership_features')) {
            SystemSetting::set('membership_features', $request->membership_features, 'json');
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
