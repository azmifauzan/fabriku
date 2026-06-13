@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-indigo-100 dark:stroke-indigo-950/40" stroke-width="3" stroke-dasharray="12 6" />
    <circle cx="100" cy="100" r="70" class="fill-indigo-50/10 dark:fill-indigo-950/5" />
    
    <!-- Floating Page/Document -->
    <g transform="translate(10, -5)">
        <rect x="60" y="45" width="70" height="90" rx="8" class="fill-white dark:fill-slate-800 stroke-slate-200 dark:stroke-slate-700" stroke-width="3" />
        <path d="M110 45V65C110 67.2091 111.791 69 114 69H130" class="fill-slate-100 dark:fill-slate-900 stroke-slate-200 dark:stroke-slate-700" stroke-width="3" />
        
        <!-- Lines on page -->
        <line x1="75" y1="80" x2="115" y2="80" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
        <line x1="75" y1="92" x2="115" y2="92" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
        <line x1="75" y1="104" x2="95" y2="104" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    </g>
    
    <!-- Question Mark -->
    <text x="100" y="105" font-family="system-ui, sans-serif" font-size="42" font-weight="900" class="fill-indigo-300 dark:fill-indigo-900/60" text-anchor="middle">?</text>
    
    <!-- Magnifying Glass -->
    <g transform="translate(70, 75)">
        <!-- Handle Shadow/Accent -->
        <path d="M47 47L68 68" class="stroke-slate-300 dark:stroke-slate-800" stroke-width="12" stroke-linecap="round" />
        <!-- Handle -->
        <path d="M47 47L68 68" class="stroke-indigo-600" stroke-width="8" stroke-linecap="round" />
        <path d="M62 62L68 68" class="stroke-purple-700" stroke-width="8" stroke-linecap="round" />
        
        <!-- Connection ring -->
        <circle cx="43" cy="43" r="5" class="fill-slate-300 dark:fill-slate-600" />
        
        <!-- Glass rim -->
        <circle cx="25" cy="25" r="23" class="fill-white/10 dark:fill-slate-900/10 stroke-indigo-500" stroke-width="4" />
        <circle cx="25" cy="25" r="23" class="stroke-purple-500" stroke-width="4" stroke-dasharray="30 15" />
        <!-- Inner glass glare -->
        <path d="M12 12C16 8 22 8 26 10" class="stroke-white/80" stroke-width="2" stroke-linecap="round" />
    </g>
</svg>
@endsection

@section('message', 'Halaman Tidak Ditemukan')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, halaman yang Anda tuju tidak dapat ditemukan. Mungkin tautan tersebut rusak atau halaman telah dihapus.' }}
@endsection
