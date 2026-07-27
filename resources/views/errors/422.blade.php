@extends('errors.layout')

@section('title', 'Data Tidak Valid')
@section('code', '422')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-amber-100 dark:stroke-amber-950/30" stroke-width="4" stroke-dasharray="10 6" />
    <circle cx="100" cy="100" r="70" class="fill-amber-50/20 dark:fill-amber-950/10" />

    <!-- Form Card -->
    <rect x="60" y="55" width="80" height="95" rx="8" class="fill-white dark:fill-slate-800 stroke-slate-200 dark:stroke-slate-700" stroke-width="3" />

    <!-- Field 1 (valid) -->
    <rect x="72" y="70" width="56" height="10" rx="3" class="fill-slate-100 dark:fill-slate-700" />
    <circle cx="122" cy="75" r="4" class="fill-emerald-400" />

    <!-- Field 2 (invalid, highlighted) -->
    <rect x="72" y="90" width="56" height="10" rx="3" class="fill-rose-50 dark:fill-rose-950/40 stroke-rose-400" stroke-width="2" />
    <circle cx="122" cy="95" r="4" class="fill-rose-500" />

    <!-- Field 3 -->
    <rect x="72" y="110" width="56" height="10" rx="3" class="fill-slate-100 dark:fill-slate-700" />

    <!-- Warning Badge -->
    <g transform="translate(128, 130)">
        <circle cx="0" cy="0" r="24" class="fill-white dark:fill-slate-800 stroke-rose-500" stroke-width="4" />
        <path d="M0 -9V4" class="stroke-rose-600" stroke-width="5" stroke-linecap="round" />
        <circle cx="0" cy="11" r="3" class="fill-rose-600" />
    </g>
</svg>
@endsection

@section('message', 'Data Tidak Valid')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, data yang Anda kirim tidak lolos validasi. Silakan periksa kembali isian formulir Anda.' }}
@endsection
