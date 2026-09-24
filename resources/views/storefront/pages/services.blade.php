<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 text-center max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold tracking-tight text-[var(--fb-text)] mb-2">Layanan Kami</h1>
        <p class="text-sm text-slate-500">Berbagai pilihan layanan profesional yang kami sediakan untuk memenuhi kebutuhan Anda.</p>
    </div>

    @include('storefront.slots.services', ['services' => $services])

    <div class="mt-16">
        @include('storefront.slots.lead-form', ['services' => $services])
    </div>
</div>
