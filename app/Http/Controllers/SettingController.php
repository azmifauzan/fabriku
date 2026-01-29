<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $settings = SystemSetting::getAllForTenant($tenantId);

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $validated = $request->validate([
            'settings' => 'required|array',
            'company_logo' => 'nullable|image|max:2048', // Max 2MB
        ]);

        // Handle file upload
        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $path = \Illuminate\Support\Facades\Storage::disk('s3')->putFile('logos', $file, 'public');
            $url = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
            SystemSetting::set('company_logo', $url, 'string', $tenantId);
        }

        // Handle other settings
        foreach ($validated['settings'] as $key => $value) {
            // Skip if value is null
            if ($value === null) continue;
            
            SystemSetting::set($key, $value, 'string', $tenantId);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
