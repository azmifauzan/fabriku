<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="/produk" class="text-sm text-slate-700 underline">← Kembali ke produk</a>
    <h1 class="mt-5 mb-2 text-3xl font-bold">Keranjang</h1>
    <p class="mb-8 text-slate-600">Toko akan mengonfirmasi ketersediaan, ongkir, dan cara bayar setelah Anda mengirim pesanan.</p>
    @if(session('success')) <p role="status" class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-emerald-900">{{ session('success') }}</p> @endif
    @if($errors->any()) <div role="alert" class="mb-6 rounded-lg border border-rose-300 bg-rose-50 p-4 text-rose-900">{{ $errors->first() }}</div> @endif

    @forelse($lines as $line)
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 py-5">
            <div>
                <a href="/produk/{{ $line['product']->slug }}" class="font-semibold underline">{{ $line['product']->title }}</a>
                <p class="text-sm text-slate-600">Mulai Rp {{ number_format($line['price'], 0, ',', '.') }} per item</p>
            </div>
            <form method="POST" action="/keranjang/ubah" class="flex items-center gap-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $line['product']->id }}">
                <label for="qty-{{ $line['product']->id }}" class="text-sm">Jumlah</label>
                <input id="qty-{{ $line['product']->id }}" type="number" name="quantity" min="0" max="50" value="{{ $line['quantity'] }}" class="w-16 rounded-lg border border-slate-400 p-2 text-center">
                <button type="submit" class="rounded-lg border border-slate-400 px-3 py-2 text-sm">Ubah</button>
            </form>
        </div>
    @empty
        <p class="rounded-lg border border-slate-200 bg-slate-50 p-8 text-center">Keranjang masih kosong.</p>
    @endforelse

    @if(count($lines) > 0)
        <p class="mt-6 text-right font-semibold">Perkiraan: Rp {{ number_format(collect($lines)->sum(fn ($line) => $line['price'] * $line['quantity']), 0, ',', '.') }}</p>
        <p class="mb-8 text-right text-sm text-slate-600">Total akhir dihitung dari harga dan stok terkini saat checkout.</p>
        <form method="POST" action="/checkout" class="grid gap-4 rounded-xl border border-slate-200 p-5 sm:p-7">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="hidden" aria-hidden="true"><label>Biarkan kosong <input type="text" name="_hp_site_order" tabindex="-1" autocomplete="off"></label></div>
            <h2 class="text-xl font-semibold">Kirim permintaan pesanan</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1 text-sm font-medium">Nama <input name="name" value="{{ old('name') }}" required autocomplete="name" maxlength="150" class="rounded-lg border border-slate-400 p-3"></label>
                <label class="grid gap-1 text-sm font-medium">WhatsApp <input name="phone" value="{{ old('phone') }}" required type="tel" autocomplete="tel" maxlength="32" class="rounded-lg border border-slate-400 p-3"></label>
            </div>
            <label class="grid gap-1 text-sm font-medium">Email (opsional) <input name="email" value="{{ old('email') }}" type="email" autocomplete="email" class="rounded-lg border border-slate-400 p-3"></label>
            <label class="grid gap-1 text-sm font-medium">Alamat pengiriman (opsional) <textarea name="address" rows="2" maxlength="1000" class="rounded-lg border border-slate-400 p-3">{{ old('address') }}</textarea></label>
            <label class="grid gap-1 text-sm font-medium">Catatan (opsional) <textarea name="notes" rows="2" maxlength="1000" class="rounded-lg border border-slate-400 p-3">{{ old('notes') }}</textarea></label>
            <button type="submit" class="rounded-lg bg-[var(--fb-primary)] px-5 py-3 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2">Kirim pesanan</button>
        </form>
    @endif
</div>
