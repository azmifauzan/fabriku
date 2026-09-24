@php
    $serviceList = $services ?? [];
@endphp

<div id="lead-form" class="bg-[var(--fb-surface)] border border-slate-200/80 rounded-[var(--fb-radius)] p-6 md:p-8 max-w-xl mx-auto shadow-sm">
    <div class="mb-6 text-center">
        <h3 class="text-xl font-bold text-[var(--fb-text)] mb-1">Minta Penawaran Layanan</h3>
        <p class="text-sm text-slate-600">Isi formulir di bawah ini dan tim kami akan segera menghubungi Anda melalui WhatsApp.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/prospek" class="space-y-4">
        @csrf
        {{-- Honeypot --}}
        <div style="display:none;">
            <input type="text" name="_hp_site_lead" value="">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Layanan (Opsional)</label>
            <select name="service_id" class="w-full text-sm border-slate-300 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus:outline-none focus:ring-2 focus:ring-[var(--fb-primary)]">
                <option value="">-- Layanan Umum / Konsultasi --</option>
                @foreach($serviceList as $svc)
                    <option value="{{ $svc->id }}">{{ $svc->name }} ({{ $svc->getPriceDisplay() }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full text-sm border-slate-300 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus:outline-none focus:ring-2 focus:ring-[var(--fb-primary)]">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp <span class="text-rose-500">*</span></label>
            <input type="tel" name="phone" required placeholder="Contoh: 081234567890" class="w-full text-sm border-slate-300 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus:outline-none focus:ring-2 focus:ring-[var(--fb-primary)]">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Kebutuhan</label>
            <textarea name="message" rows="3" placeholder="Jelaskan kebutuhan atau spesifikasi pekerjaan Anda..." class="w-full text-sm border-slate-300 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus:outline-none focus:ring-2 focus:ring-[var(--fb-primary)]"></textarea>
        </div>

        <div class="flex items-start gap-2 pt-1">
            <input type="checkbox" name="consent" id="lead_consent" value="1" required class="mt-1 rounded text-[var(--fb-primary)] focus:ring-[var(--fb-primary)]">
            <label for="lead_consent" class="text-xs text-slate-600 leading-relaxed">
                Saya bersedia dihubungi oleh pihak toko terkait penawaran layanan ini sesuai dengan UU Perlindungan Data Pribadi (UU No. 27/2022).
            </label>
        </div>

        <button type="submit" class="w-full py-2.5 px-4 rounded-[var(--fb-radius)] bg-[var(--fb-primary)] text-white font-semibold text-sm hover:opacity-90 transition">
            Kirim Permintaan
        </button>
    </form>
</div>
