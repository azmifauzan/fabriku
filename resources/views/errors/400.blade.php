@extends('errors.layout')

@section('title', 'Permintaan Tidak Valid')
@section('code', '400')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-amber-100 dark:stroke-amber-950/30" stroke-width="4" stroke-dasharray="10 6" />
    <circle cx="100" cy="100" r="70" class="fill-amber-50/20 dark:fill-amber-950/10" />

    <!-- Document -->
    <rect x="65" y="50" width="70" height="100" rx="8" class="fill-white dark:fill-slate-800 stroke-slate-200 dark:stroke-slate-700" stroke-width="3" />
    <path d="M115 50V70C115 72.2091 116.791 74 119 74H135" class="fill-slate-100 dark:fill-slate-900 stroke-slate-200 dark:stroke-slate-700" stroke-width="3" />

    <!-- Broken lines (invalid content) -->
    <line x1="80" y1="90" x2="110" y2="90" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    <line x1="80" y1="102" x2="120" y2="102" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    <line x1="80" y1="114" x2="100" y2="114" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />

    <!-- Warning Badge -->
    <g transform="translate(128, 128)">
        <circle cx="0" cy="0" r="26" class="fill-white dark:fill-slate-800 stroke-amber-500" stroke-width="4" />
        <path d="M0 -10V4" class="stroke-amber-600" stroke-width="5" stroke-linecap="round" />
        <circle cx="0" cy="12" r="3" class="fill-amber-600" />
    </g>
</svg>
@endsection

@section('message', 'Permintaan Tidak Valid')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, permintaan yang Anda kirim tidak dapat diproses. Silakan periksa kembali data yang dikirim.' }}
@endsection
