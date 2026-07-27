@extends('errors.layout')

@section('title', 'Metode Tidak Diizinkan')
@section('code', '405')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-amber-100 dark:stroke-amber-950/30" stroke-width="4" stroke-dasharray="10 6" />
    <circle cx="100" cy="100" r="70" class="fill-amber-50/20 dark:fill-amber-950/10" />

    <!-- Signpost -->
    <rect x="96" y="80" width="8" height="70" rx="3" class="fill-slate-300 dark:fill-slate-700" />

    <!-- Directional signs -->
    <g transform="translate(100, 85)">
        <path d="M-45 0H15L0 15L15 30H-45Z" class="fill-white dark:fill-slate-800 stroke-amber-500" stroke-width="3" stroke-linejoin="round" />
    </g>
    <g transform="translate(100, 110)">
        <path d="M45 0H-15L0 15L-15 30H45Z" class="fill-white dark:fill-slate-800 stroke-slate-400 dark:stroke-slate-600" stroke-width="3" stroke-linejoin="round" />
    </g>

    <!-- Block/No-entry Badge -->
    <g transform="translate(128, 62)">
        <circle cx="0" cy="0" r="22" class="fill-white dark:fill-slate-800 stroke-rose-500" stroke-width="4" />
        <line x1="-11" y1="-11" x2="11" y2="11" class="stroke-rose-500" stroke-width="4" stroke-linecap="round" />
    </g>
</svg>
@endsection

@section('message', 'Metode Tidak Diizinkan')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, cara mengakses halaman ini tidak diizinkan. Silakan kembali dan coba cara lain.' }}
@endsection
