@extends('errors.layout')

@section('title', 'Sesi Telah Berakhir')
@section('code', '419')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-amber-100 dark:stroke-amber-950/30" stroke-width="4" stroke-dasharray="8 8" />
    <circle cx="100" cy="100" r="70" class="fill-amber-50/20 dark:fill-amber-950/10" />
    
    <!-- Refresh circular arrow (represents reloading session) -->
    <path d="M145 70C155 85 155 105 145 120C135 135 115 145 100 145" class="stroke-amber-500/50" stroke-width="3" stroke-linecap="round" stroke-dasharray="6 6" />
    <path d="M55 130C45 115 45 95 55 80C65 65 85 55 100 55" class="stroke-indigo-500/50" stroke-width="3" stroke-linecap="round" stroke-dasharray="6 6" />
    
    <!-- Hourglass Frame -->
    <path d="M70 50H130" class="stroke-slate-700 dark:stroke-slate-300" stroke-width="6" stroke-linecap="round" />
    <path d="M70 150H130" class="stroke-slate-700 dark:stroke-slate-300" stroke-width="6" stroke-linecap="round" />
    
    <!-- Hourglass Glass Body -->
    <path d="M75 53C75 80 95 95 95 100C95 105 75 120 75 147" class="stroke-slate-400 dark:stroke-slate-600 fill-white dark:fill-slate-800" stroke-width="3" />
    <path d="M125 53C125 80 105 95 105 100C105 105 125 120 125 147" class="stroke-slate-400 dark:stroke-slate-600 fill-white dark:fill-slate-800" stroke-width="3" />
    
    <!-- Top Sand (Emptying) -->
    <path d="M80 56C80 75 97 85 97 90H103C103 85 120 75 120 56H80Z" class="fill-amber-400" />
    
    <!-- Sand Stream -->
    <line x1="100" y1="90" x2="100" y2="144" class="stroke-amber-400" stroke-width="3" stroke-dasharray="8 4" />
    
    <!-- Bottom Sand (Filled) -->
    <path d="M78 144C78 135 90 125 100 125C110 125 122 135 122 144H78Z" class="fill-amber-400 animate-pulse" />
    
    <!-- Glowing Expiry Sparkles -->
    <circle cx="140" cy="55" r="3" class="fill-indigo-500 animate-ping" />
    <circle cx="60" cy="140" r="2" class="fill-amber-500 animate-ping" />
</svg>
@endsection

@section('message', 'Sesi Telah Berakhir')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, halaman ini telah kedaluwarsa karena Anda tidak aktif dalam waktu yang cukup lama. Silakan segarkan halaman dan coba lagi.' }}
@endsection
