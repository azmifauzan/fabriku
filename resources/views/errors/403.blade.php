@extends('errors.layout')

@section('title', 'Akses Dibatasi')
@section('code', '403')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-rose-100 dark:stroke-rose-950/30" stroke-width="4" stroke-dasharray="10 6" />
    <circle cx="100" cy="100" r="70" class="fill-rose-50/20 dark:fill-rose-950/10" />
    
    <!-- Shield Base -->
    <path d="M100 40C125 40 145 48 145 48V100C145 130 125 152 100 162C75 152 55 130 55 100V48C55 48 75 40 100 40Z" class="fill-white dark:fill-slate-800 stroke-rose-500/80 dark:stroke-rose-500/40" stroke-width="4" stroke-linejoin="round" />
    
    <!-- Shield Accent Lines -->
    <path d="M100 44V157" class="stroke-slate-100 dark:stroke-slate-900" stroke-width="2" />
    
    <!-- Padlock Outer Arc -->
    <path d="M85 96V85C85 76.7157 91.7157 70 100 70C108.284 70 115 76.7157 115 85V96" class="stroke-rose-500" stroke-width="5" stroke-linecap="round" />
    
    <!-- Padlock Body -->
    <rect x="74" y="94" width="52" height="42" rx="10" class="fill-rose-500" />
    <rect x="80" y="100" width="40" height="30" rx="6" class="fill-rose-600" />
    
    <!-- Keyhole -->
    <circle cx="100" cy="112" r="4" class="fill-white" />
    <path d="M100 115V124" class="stroke-white" stroke-width="3" stroke-linecap="round" />
</svg>
@endsection

@section('message', 'Akses Dibatasi')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki hak akses (izin) yang diperlukan untuk membuka halaman ini.' }}
@endsection
