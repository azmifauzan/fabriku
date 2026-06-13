@extends('errors.layout')

@section('title', 'Layanan Sedang Dipelihara')
@section('code', '503')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-indigo-100 dark:stroke-indigo-950/30" stroke-width="4" stroke-dasharray="10 5" />
    <circle cx="100" cy="100" r="70" class="fill-indigo-50/20 dark:fill-indigo-950/10" />
    
    <!-- Clouds representing cloud servers -->
    <path d="M50 85C45 85 41 89 41 94C41 99 45 103 50 103H150C155 103 159 99 159 94C159 89 155 85 150 85" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    
    <!-- Server Rack Cabinet -->
    <rect x="65" y="60" width="70" height="90" rx="10" class="fill-white dark:fill-slate-800 stroke-slate-300 dark:stroke-slate-700" stroke-width="3" />
    
    <!-- Server Slots -->
    <!-- Slot 1 -->
    <rect x="73" y="72" width="54" height="14" rx="3" class="fill-slate-50 dark:fill-slate-900 stroke-slate-200 dark:stroke-slate-700" stroke-width="2" />
    <circle cx="80" cy="79" r="2.5" class="fill-emerald-500 animate-pulse" />
    <circle cx="88" cy="79" r="2.5" class="fill-emerald-400" />
    <line x1="98" y1="79" x2="118" y2="79" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="2" stroke-linecap="round" />

    <!-- Slot 2 (Active/Under Work) -->
    <rect x="73" y="94" width="54" height="14" rx="3" class="fill-indigo-50 dark:fill-slate-900 stroke-indigo-200 dark:stroke-indigo-800" stroke-width="2" />
    <circle cx="80" cy="101" r="2.5" class="fill-indigo-500 animate-ping" />
    <circle cx="80" cy="101" r="2.5" class="fill-indigo-500" />
    <circle cx="88" cy="101" r="2.5" class="fill-slate-300 dark:fill-slate-700" />
    <line x1="98" y1="101" x2="118" y2="101" class="stroke-indigo-200 dark:stroke-indigo-800" stroke-width="2" stroke-linecap="round" />

    <!-- Slot 3 -->
    <rect x="73" y="116" width="54" height="14" rx="3" class="fill-slate-50 dark:fill-slate-900 stroke-slate-200 dark:stroke-slate-700" stroke-width="2" />
    <circle cx="80" cy="123" r="2.5" class="fill-slate-300 dark:fill-slate-600" />
    <circle cx="88" cy="123" r="2.5" class="fill-slate-300 dark:fill-slate-600" />
    <line x1="98" y1="123" x2="118" y2="123" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="2" stroke-linecap="round" />

    <!-- Builder Hard Hat or Wrench -->
    <!-- Wrench resting on top/side -->
    <g transform="translate(130, 115) rotate(-35)">
        <path d="M-6 -25 L6 -25 L4 -5 L-4 -5 Z" class="fill-amber-500 stroke-slate-700 dark:stroke-slate-300" stroke-width="2" />
        <circle cx="0" cy="5" r="10" class="fill-amber-500 stroke-slate-700 dark:stroke-slate-300" stroke-width="2" />
        <rect x="-3" y="2" width="6" height="8" class="fill-white dark:fill-slate-900" />
    </g>
    
    <!-- Barrier Tape / Warning Lines -->
    <path d="M40 145 L160 145" class="stroke-amber-400" stroke-width="8" stroke-linecap="round" />
    <path d="M50 145 L65 145 M80 145 L95 145 M110 145 L125 145 M140 145 L155 145" class="stroke-slate-900" stroke-width="8" stroke-linecap="round" />
</svg>
@endsection

@section('message', 'Pemeliharaan Sistem')

@section('description')
{{ $exception->getMessage() ?: 'Kami sedang melakukan pemeliharaan berkala untuk meningkatkan performa aplikasi. Silakan kembali dalam beberapa saat lagi.' }}
@endsection
