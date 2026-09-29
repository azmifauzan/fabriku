<div class="mx-auto max-w-2xl px-4 py-12">
    <h1 class="text-3xl font-bold">Laporkan situs ini</h1>
    <p class="mt-2 mb-6 text-slate-600">Laporan dikirim ke tim Fabriku, bukan ke pengelola toko. Jelaskan masalahnya agar kami bisa meninjau dengan tepat.</p>
    @if(session('success')) <p role="status" class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-emerald-900">{{ session('success') }}</p> @endif
    @if($errors->any()) <p role="alert" class="mb-4 rounded-lg border border-rose-300 bg-rose-50 p-4 text-rose-900">{{ $errors->first() }}</p> @endif
    <form method="POST" action="/lapor" class="grid gap-4 rounded-xl border border-slate-200 p-5 sm:p-7">
        @csrf
        <div class="hidden" aria-hidden="true"><label>Biarkan kosong <input type="text" name="_hp_site_report" tabindex="-1" autocomplete="off"></label></div>
        <label class="grid gap-1 text-sm font-medium">Jenis masalah<select name="category" required class="rounded-lg border border-slate-400 p-3"><option value="">Pilih jenis laporan</option><option value="penipuan" @selected(old('category') === 'penipuan')>Dugaan penipuan</option><option value="barang_terlarang" @selected(old('category') === 'barang_terlarang')>Barang atau jasa terlarang</option><option value="privasi" @selected(old('category') === 'privasi')>Masalah privasi</option><option value="lainnya" @selected(old('category') === 'lainnya')>Lainnya</option></select></label>
        <label class="grid gap-1 text-sm font-medium">Penjelasan<textarea name="details" required minlength="10" maxlength="3000" rows="5" class="rounded-lg border border-slate-400 p-3">{{ old('details') }}</textarea></label>
        <label class="grid gap-1 text-sm font-medium">Email untuk tindak lanjut (opsional)<input name="contact_email" type="email" value="{{ old('contact_email') }}" class="rounded-lg border border-slate-400 p-3"></label>
        <button type="submit" class="rounded-lg bg-teal-800 px-4 py-3 font-semibold text-white">Kirim laporan</button>
    </form>
</div>
