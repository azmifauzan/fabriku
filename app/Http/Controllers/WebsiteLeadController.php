<?php

namespace App\Http\Controllers;

use App\Models\BusinessSite;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesOrder;
use App\Models\Scopes\TenantScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WebsiteLeadController extends Controller
{
    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request, $lead);
        $data = $request->validate([
            'status' => ['required', Rule::in(['contacted', 'closed'])],
            'closed_reason' => ['required_if:status,closed', 'nullable', 'string', 'max:500'],
        ]);
        abort_if($lead->status === 'converted', 422);
        $lead->update([
            'status' => $data['status'],
            'contacted_at' => $data['status'] === 'contacted' ? ($lead->contacted_at ?? now()) : $lead->contacted_at,
            'closed_reason' => $data['status'] === 'closed' ? $data['closed_reason'] : null,
            'assigned_user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Status prospek diperbarui.');
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request, $lead);
        abort_unless($lead->service_id, 422, 'Pilih layanan sebelum mengubah prospek menjadi pesanan.');
        DB::transaction(function () use ($lead, $request) {
            $locked = Lead::whereKey($lead->id)->lockForUpdate()->firstOrFail();
            if ($locked->sales_order_id) {
                return;
            }
            $service = $locked->service;
            abort_unless($service && $service->tenant_id === $locked->tenant_id, 422);
            $phone = preg_replace('/\D+/', '', $locked->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            }
            $customer = Customer::withoutGlobalScope(TenantScope::class)->firstOrCreate(
                ['tenant_id' => $locked->tenant_id, 'phone' => $phone],
                ['code' => 'CUST-'.strtoupper(Str::random(10)), 'name' => $locked->name, 'is_active' => true]
            );
            $price = $service->price_label === 'hubungi_kami' ? 0 : (float) $service->price;
            $order = SalesOrder::create([
                'tenant_id' => $locked->tenant_id, 'customer_id' => $customer->id,
                'order_date' => today(), 'channel' => 'online', 'status' => 'draft',
                'subtotal' => $price, 'total_amount' => $price, 'payment_status' => 'unpaid', 'paid_amount' => 0,
                'notes' => 'Dari prospek website #'.$locked->id.($locked->message ? ': '.$locked->message : ''),
            ]);
            $order->items()->create(['service_id' => $service->id, 'quantity' => 1, 'unit_price' => $price, 'discount_amount' => 0, 'subtotal' => $price]);
            $locked->update(['status' => 'converted', 'sales_order_id' => $order->id, 'assigned_user_id' => $request->user()->id, 'contacted_at' => $locked->contacted_at ?? now()]);
        });

        return back()->with('success', 'Prospek diubah menjadi pesanan draft.');
    }

    private function authorizeLead(Request $request, Lead $lead): void
    {
        $site = BusinessSite::where('tenant_id', $request->user()->tenant_id)->firstOrFail();
        abort_unless($lead->tenant_id === $site->tenant_id && $lead->business_site_id === $site->id, 404);
        abort_unless($request->user()->isManager() || $site->recipients()->whereKey($request->user()->id)->exists(), 403);
    }
}
