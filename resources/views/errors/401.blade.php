@extends('errors.layout')

@section('title', 'Akses Tidak Diizinkan')
@section('code', '401')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-indigo-100 dark:stroke-indigo-950/50" stroke-width="4" stroke-dasharray="8 8" />
    <circle cx="100" cy="100" r="70" class="fill-indigo-50/30 dark:fill-indigo-950/20" />
    
    <!-- ID Card Base -->
    <rect x="60" y="50" width="80" height="100" rx="12" class="fill-white dark:fill-slate-800 stroke-slate-200 dark:stroke-slate-700" stroke-width="3" />
    
    <!-- ID Card Header -->
    <path d="M60 62C60 56.4772 64.4772 52 70 52H130C135.523 52 140 56.4772 140 62V75H60V62Z" class="fill-indigo-500 dark:fill-indigo-600" />
    
    <!-- ID Card Photo Slot -->
    <rect x="85" y="85" width="30" height="35" rx="6" class="fill-slate-100 dark:fill-slate-900 stroke-slate-200 dark:stroke-slate-700" stroke-width="2" />
    
    <!-- Photo Avatar Placeholder -->
    <circle cx="100" cy="97" r="7" class="fill-slate-300 dark:fill-slate-600" />
    <path d="M88 116C88 110.477 92.4772 106 98 106H102C107.523 106 112 110.477 112 116V118H88V116Z" class="fill-slate-300 dark:fill-slate-600" />
    
    <!-- ID Card Details Lines -->
    <line x1="75" y1="132" x2="125" y2="132" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    <line x1="85" y1="140" x2="115" y2="140" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    
    <!-- Lock Badge -->
    <circle cx="130" cy="130" r="22" class="fill-rose-500 shadow-lg" />
    <circle cx="130" cy="130" r="22" class="stroke-white dark:stroke-slate-900" stroke-width="3" />
    
    <!-- Lock Icon inside Badge -->
    <path d="M125 131V127C125 124.239 127.239 122 130 122C132.761 122 135 124.239 135 127V131" class="stroke-white" stroke-width="2" stroke-linecap="round" />
    <rect x="122" y="129" width="16" height="12" rx="3" class="fill-white" />
    <circle cx="130" cy="135" r="1.5" class="fill-rose-500" />
</svg>
@endsection

@section('message', 'Akses Tidak Diizinkan')

@section('description')
{{ $exception->getMessage() ?: 'Maaf, Anda memerlukan autentikasi sebelum dapat mengakses halaman ini. Silakan masuk terlebih dahulu.' }}
@endsection
