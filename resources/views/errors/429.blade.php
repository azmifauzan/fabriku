@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-orange-100 dark:stroke-orange-950/30" stroke-width="4" stroke-dasharray="10 5" />
    <circle cx="100" cy="100" r="70" class="fill-orange-50/20 dark:fill-orange-950/10" />
    
    <!-- Speedometer Dial Arc -->
    <path d="M50 130C38 110 38 85 50 65C65 40 95 30 125 35C150 40 162 60 162 60" class="stroke-slate-300 dark:stroke-slate-700" stroke-width="8" stroke-linecap="round" />
    <!-- Speedometer Red Zone -->
    <path d="M125 35C140 38 152 48 162 60" class="stroke-rose-500" stroke-width="10" stroke-linecap="round" />
    
    <!-- Speedometer ticks -->
    <line x1="58" y1="116" x2="65" y2="112" class="stroke-slate-400 dark:stroke-slate-500" stroke-width="3" />
    <line x1="52" y1="80" x2="60" y2="80" class="stroke-slate-400 dark:stroke-slate-500" stroke-width="3" />
    <line x1="68" y1="52" x2="74" y2="58" class="stroke-slate-400 dark:stroke-slate-500" stroke-width="3" />
    <line x1="100" y1="40" x2="100" y2="48" class="stroke-slate-400 dark:stroke-slate-500" stroke-width="3" />
    <line x1="132" y1="52" x2="126" y2="58" class="stroke-rose-400" stroke-width="3" />
    
    <!-- Speedometer center pin -->
    <circle cx="100" cy="110" r="14" class="fill-slate-800 dark:fill-slate-200" />
    <circle cx="100" cy="110" r="6" class="fill-slate-600 dark:fill-slate-400" />
    
    <!-- Speedometer Needle (Hitting the Red Zone on the right) -->
    <g transform="rotate(55, 100, 110)">
        <path d="M97 110L100 45L103 110Z" class="fill-rose-500" />
    </g>
    
    <!-- Speed/limit Warning badge -->
    <rect x="75" y="135" width="50" height="26" rx="6" class="fill-white dark:fill-slate-800 stroke-rose-500" stroke-width="2" />
    <text x="100" y="152" font-family="system-ui, sans-serif" font-size="12" font-weight="800" class="fill-rose-500" text-anchor="middle">LIMIT</text>
</svg>
@endsection

@section('message', 'Terlalu Banyak Permintaan')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, Anda melakukan terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba kembali.' }}
@endsection
