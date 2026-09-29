<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyStorefrontRequest;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\Scopes\TenantScope;
use App\Models\SiteProduct;
use App\Services\Storefront\Storefront;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function add(Request $request): RedirectResponse
    {
        $tenant = Storefront::currentTenant();
        $site = Storefront::currentSite();
        abort_unless($tenant && $site && $site->mode !== 'jasa' && $site->canAcceptOrders(), 404);

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);
        $product = Storefront::for($tenant)->products()->findOrFail($data['product_id']);
        abort_unless($product->isAvailable() && $product->getStartingPrice() > 0, 422);

        $key = $this->sessionKey($tenant->id);
        $cart = $request->session()->get($key, []);
        if (count($cart) >= 20 && ! isset($cart[$product->id])) {
            throw ValidationException::withMessages(['product_id' => 'Keranjang maksimal 20 produk.']);
        }
        $cart[$product->id] = min(50, ($cart[$product->id] ?? 0) + $data['quantity']);
        $request->session()->put($key, $cart);

        return redirect('/keranjang')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = Storefront::currentTenant();
        abort_unless($tenant && Storefront::currentSite()?->mode !== 'jasa', 404);
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);
        $key = $this->sessionKey($tenant->id);
        $cart = $request->session()->get($key, []);
        if ($data['quantity'] === 0) {
            unset($cart[$data['product_id']]);
        } else {
            Storefront::for($tenant)->products()->findOrFail($data['product_id']);
            $cart[$data['product_id']] = $data['quantity'];
        }
        $request->session()->put($key, $cart);

        return back();
    }

    public function checkout(Request $request): RedirectResponse
    {
        $tenant = Storefront::currentTenant();
        $site = Storefront::currentSite();
        abort_unless($tenant && $site && $site->mode !== 'jasa' && $site->canAcceptOrders(), 404);

        if ($request->filled('_hp_site_order')) {
            return redirect('/keranjang')->with('success', 'Permintaan Anda diterima.');
        }
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+().\-\s]{7,32}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $existing = SalesOrder::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)->where('storefront_checkout_key', $data['idempotency_key'])->first();
        if ($existing) {
            return redirect('/keranjang')->with('success', 'Pesanan '.$existing->order_number.' sudah diterima.');
        }

        $cart = $request->session()->get($this->sessionKey($tenant->id), []);
        if (! $cart) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        $order = null;
        $created = false;
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $order = DB::transaction(function () use ($tenant, $cart, $data) {
                    $products = Storefront::for($tenant)->products()->whereIn('id', array_keys($cart))->get()->keyBy('id');
                    if ($products->count() !== count($cart)) {
                        throw ValidationException::withMessages(['cart' => 'Ada produk yang sudah tidak tersedia. Periksa keranjang kembali.']);
                    }
                    $lines = [];
                    $total = 0;
                    foreach ($cart as $id => $quantity) {
                        $product = $products->get((int) $id);
                        $remaining = (int) $quantity;
                        $inventory = InventoryItem::withoutGlobalScope(TenantScope::class)
                            ->where('tenant_id', $tenant->id)->where('product_code', $product->product_code)
                            ->where('status', 'available')->where('selling_price', '>', 0)
                            ->orderBy('selling_price')->orderBy('id')->get();
                        foreach ($inventory as $item) {
                            $take = min($remaining, max(0, (int) $item->current_quantity - (int) $item->reserved_quantity));
                            if ($take > 0) {
                                $subtotal = $take * (float) $item->selling_price;
                                $lines[] = ['inventory_item_id' => $item->id, 'quantity' => $take, 'unit_price' => $item->selling_price, 'discount_amount' => 0, 'subtotal' => $subtotal];
                                $total += $subtotal;
                                $remaining -= $take;
                            }
                            if ($remaining === 0) {
                                break;
                            }
                        }
                        if ($remaining > 0) {
                            throw ValidationException::withMessages(['cart' => 'Stok '.$product->title.' tidak mencukupi.']);
                        }
                    }

                    $phone = preg_replace('/\D+/', '', $data['phone']);
                    if (str_starts_with($phone, '0')) {
                        $phone = '62'.substr($phone, 1);
                    }
                    $customer = Customer::withoutGlobalScope(TenantScope::class)->firstOrCreate(
                        ['tenant_id' => $tenant->id, 'phone' => $phone],
                        ['code' => 'CUST-'.strtoupper(Str::random(10)), 'name' => $data['name'], 'email' => $data['email'] ?? null, 'is_active' => true]
                    );
                    $order = SalesOrder::create([
                        'tenant_id' => $tenant->id,
                        'customer_id' => $customer->id,
                        'storefront_checkout_key' => $data['idempotency_key'],
                        'order_date' => today(),
                        'channel' => 'online',
                        'status' => 'draft',
                        'subtotal' => $total,
                        'total_amount' => $total,
                        'payment_status' => 'unpaid',
                        'paid_amount' => 0,
                        'shipping_address' => $data['address'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]);
                    $order->items()->createMany($lines);

                    return $order;
                });
                $created = true;
                break;
            } catch (UniqueConstraintViolationException $e) {
                $order = SalesOrder::withoutGlobalScope(TenantScope::class)
                    ->where('tenant_id', $tenant->id)->where('storefront_checkout_key', $data['idempotency_key'])->first();
                if ($order) {
                    break;
                }
                if ($attempt === 2) {
                    throw $e;
                }
            }
        }
        $request->session()->forget($this->sessionKey($tenant->id));
        if ($created) {
            NotifyStorefrontRequest::dispatch($site->id, 'order', $order->id)->afterCommit();
        }

        return redirect('/keranjang')->with('success', 'Pesanan '.$order->order_number.' diterima. Tim toko akan menghubungi Anda untuk konfirmasi dan pembayaran.');
    }

    public static function lines(Request $request, int $tenantId): array
    {
        $cart = $request->session()->get("storefront_cart.{$tenantId}", []);
        $products = SiteProduct::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)->where('is_visible', true)->whereIn('id', array_keys($cart))->get();

        return $products->map(fn (SiteProduct $product) => [
            'product' => $product,
            'quantity' => $cart[$product->id],
            'price' => $product->getStartingPrice(),
        ])->all();
    }

    private function sessionKey(int $tenantId): string
    {
        return "storefront_cart.{$tenantId}";
    }
}
