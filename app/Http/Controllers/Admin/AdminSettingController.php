<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\SystemSetting::getAllForTenant(null);

        return \Inertia\Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_holder' => 'required|string',
            'membership_price_monthly' => 'required|numeric',
            'membership_price_yearly' => 'required|numeric',
        ]);

        \App\Models\SystemSetting::set('bank_name', $request->bank_name);
        \App\Models\SystemSetting::set('account_number', $request->account_number);
        \App\Models\SystemSetting::set('account_holder', $request->account_holder);
        \App\Models\SystemSetting::set('membership_price_monthly', $request->membership_price_monthly, 'number');
        \App\Models\SystemSetting::set('membership_price_yearly', $request->membership_price_yearly, 'number');

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
