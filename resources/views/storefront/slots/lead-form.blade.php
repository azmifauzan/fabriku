@php
    $serviceList = $services ?? [];
@endphp

<div id="lead-form" class="bg-[var(--fb-surface)] border border-slate-200/80 rounded-[var(--fb-radius)] p-6 md:p-8 max-w-xl mx-auto shadow-sm">
    <div class="mb-6 text-center">
        <h3 class="text-xl font-bold text-[var(--fb-text)] mb-1">Minta Penawaran Layanan</h3>
        <p class="text-sm text-slate-600">Isi formulir di bawah ini dan tim kami akan segera menghubungi Anda melalui WhatsApp.</p>
    </div>

    @if(session('success'))
        <div role="status" aria-live="polite" class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/prospek" class="space-y-4">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
        {{-- Honeypot --}}
        <div style="display:none;">
            <input type="text" name="_hp_site_lead" value="">
        </div>

        <div>
            <label for="service_id" class="block text-xs font-semibold text-slate-700 mb-1">Pilih Layanan (Opsional)</label>
            <select id="service_id" name="service_id" class="w-full text-sm border-slate-500 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2" @if($errors->has('service_id')) aria-describedby="service_id-error" aria-invalid="true" @endif>
                <option value="">-- Layanan Umum / Konsultasi --</option>
                @foreach($serviceList as $svc)
                    <option value="{{ $svc->id }}" @selected((string) old('service_id') === (string) $svc->id)>{{ $svc->name }} ({{ $svc->getPriceDisplay() }})</option>
                @endforeach
            </select>
            @error('service_id') <p id="service_id-error" role="alert" class="mt-1 text-sm font-semibold text-slate-900">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required placeholder="Nama Anda" class="w-full text-sm border-slate-500 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2" @if($errors->has('name')) aria-describedby="name-error" aria-invalid="true" @endif>
            @error('name') <p id="name-error" role="alert" class="mt-1 text-sm font-semibold text-slate-900">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp <span class="text-rose-500">*</span></label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required placeholder="Contoh: 081234567890" class="w-full text-sm border-slate-500 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2" @if($errors->has('phone')) aria-describedby="phone-error" aria-invalid="true" @endif>
            @error('phone') <p id="phone-error" role="alert" class="mt-1 text-sm font-semibold text-slate-900">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="message" class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Kebutuhan</label>
            <textarea id="message" name="message" rows="3" placeholder="Jelaskan kebutuhan atau spesifikasi pekerjaan Anda..." class="w-full text-sm border-slate-500 rounded-[calc(var(--fb-radius)-2px)] px-3 py-2 border bg-white focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2" @if($errors->has('message')) aria-describedby="message-error" aria-invalid="true" @endif>{{ old('message') }}</textarea>
            @error('message') <p id="message-error" role="alert" class="mt-1 text-sm font-semibold text-slate-900">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-start gap-2 pt-1">
            <input type="checkbox" name="consent" id="lead_consent" value="1" required @checked(old('consent')) class="mt-1 rounded text-[var(--fb-primary)] focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2" @if($errors->has('consent')) aria-describedby="consent-error" aria-invalid="true" @endif>
            <label for="lead_consent" class="text-xs text-slate-600 leading-relaxed">
                Saya bersedia dihubungi oleh pihak toko terkait penawaran layanan ini sesuai dengan UU Perlindungan Data Pribadi (UU No. 27/2022).
            </label>
        </div>
        @error('consent') <p id="consent-error" role="alert" class="text-sm font-semibold text-slate-900">{{ $message }}</p> @enderror

        <button type="submit" class="w-full py-2.5 px-4 rounded-[var(--fb-radius)] bg-[var(--fb-primary)] text-white font-semibold text-sm hover:opacity-90 transition focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
            Kirim Permintaan
        </button>
    </form>
</div>
