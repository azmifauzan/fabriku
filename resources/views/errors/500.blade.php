@extends('errors.layout')

@section('title', 'Kesalahan Server Internal')
@section('code', '500')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-rose-100 dark:stroke-rose-950/30" stroke-width="4" stroke-dasharray="6 6" />
    <circle cx="100" cy="100" r="70" class="fill-rose-50/20 dark:fill-rose-950/10" />
    
    <!-- Big Gear (Left-ish, central) -->
    <g transform="translate(85, 95) rotate(15)">
        <circle cx="0" cy="0" r="26" class="fill-slate-300 dark:fill-slate-700 stroke-slate-400 dark:stroke-slate-600" stroke-width="3" />
        <!-- Gear Teeth -->
        @for ($i = 0; $i < 8; $i++)
            <rect x="-6" y="-34" width="12" height="12" rx="2" class="fill-slate-300 dark:fill-slate-700 stroke-slate-400 dark:stroke-slate-600" stroke-width="2" transform="rotate({{ $i * 45 }})" />
        @endfor
        <!-- Gear Center -->
        <circle cx="0" cy="0" r="8" class="fill-white dark:fill-slate-900" />
    </g>

    <!-- Small Gear (Right-ish, central, jam-inducing) -->
    <g transform="translate(122, 72) rotate(45)">
        <circle cx="0" cy="0" r="18" class="fill-slate-400 dark:fill-slate-600 stroke-slate-500 dark:stroke-slate-500" stroke-width="2" />
        <!-- Gear Teeth -->
        @for ($i = 0; $i < 6; $i++)
            <rect x="-4" y="-23" width="8" height="8" rx="1.5" class="fill-slate-400 dark:fill-slate-600 stroke-slate-500 dark:stroke-slate-500" stroke-width="2" transform="rotate({{ $i * 60 }})" />
        @endfor
        <!-- Gear Center -->
        <circle cx="0" cy="0" r="6" class="fill-white dark:fill-slate-900" />
    </g>

    <!-- Warning Hazard Triangle (Overlaying the gears) -->
    <g transform="translate(100, 100)">
        <!-- Triangle Glow -->
        <polygon points="0,-48 -42,24 42,24" class="fill-white dark:fill-slate-800 stroke-rose-500" stroke-width="5" stroke-linejoin="round" />
        <polygon points="0,-40 -34,18 34,18" class="fill-rose-50" />
        
        <!-- Exclamation Symbol -->
        <path d="M0 -16V2" class="stroke-rose-600" stroke-width="5" stroke-linecap="round" />
        <circle cx="0" cy="11" r="3" class="fill-rose-600" />
    </g>
    
    <!-- Sparks/Glitch Indicators -->
    <path d="M45 70L35 60M155 130L165 140" class="stroke-rose-400" stroke-width="3" stroke-linecap="round" />
</svg>
@endsection

@section('message', 'Kesalahan Server Internal')

@section('description')
{{ config('app.debug') && $exception->getMessage() ? $exception->getMessage() : 'Terjadi kesalahan internal pada server kami. Jangan khawatir, tim teknis kami telah menerima notifikasi dan sedang memperbaikinya.' }}
@endsection
