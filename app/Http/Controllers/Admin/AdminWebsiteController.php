<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSite;
use App\Models\SiteReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminWebsiteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Websites/Index', [
            'sites' => BusinessSite::with('tenant:id,name')->latest()->paginate(20),
            'reports' => SiteReport::with('businessSite:id,slug,tenant_id')->latest()->limit(100)->get(),
        ]);
    }

    public function status(Request $request, BusinessSite $site): RedirectResponse
    {
        $data = $request->validate(['action' => ['required', 'in:suspend,restore']]);
        if ($data['action'] === 'suspend') {
            $site->update(['status' => 'suspended']);
        } elseif ($site->isSuspended()) {
            $site->update(['status' => 'paused']);
        }

        return back()->with('success', 'Status situs diperbarui.');
    }

    public function report(Request $request, SiteReport $report): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:reviewed,resolved']]);
        $report->update($data);

        return back()->with('success', 'Laporan diperbarui.');
    }
}
