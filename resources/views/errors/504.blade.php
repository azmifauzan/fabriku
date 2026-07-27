@extends('errors.layout')

@section('title', 'Waktu Permintaan Habis')
@section('code', '504')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-rose-100 dark:stroke-rose-950/30" stroke-width="4" stroke-dasharray="6 6" />
    <circle cx="100" cy="100" r="70" class="fill-rose-50/20 dark:fill-rose-950/10" />

    <!-- Clock Face -->
    <circle cx="100" cy="105" r="50" class="fill-white dark:fill-slate-800 stroke-slate-300 dark:stroke-slate-700" stroke-width="4" />
    <circle cx="100" cy="105" r="42" class="stroke-slate-100 dark:stroke-slate-700" stroke-width="2" stroke-dasharray="3 6" />

    <!-- Clock Hands (stuck/slow) -->
    <path d="M100 105V75" class="stroke-slate-500 dark:stroke-slate-400" stroke-width="4" stroke-linecap="round" />
    <path d="M100 105L124 118" class="stroke-rose-500" stroke-width="4" stroke-linecap="round" />
    <circle cx="100" cy="105" r="5" class="fill-slate-600 dark:fill-slate-300" />

    <!-- Clock Bell/Top -->
    <rect x="94" y="48" width="12" height="8" rx="3" class="fill-slate-400 dark:fill-slate-600" />

    <!-- Alert Badge -->
    <g transform="translate(140, 60)">
        <circle cx="0" cy="0" r="20" class="fill-white dark:fill-slate-800 stroke-rose-500" stroke-width="4" />
        <path d="M0 -8V3" class="stroke-rose-600" stroke-width="4" stroke-linecap="round" />
        <circle cx="0" cy="9" r="2.5" class="fill-rose-600" />
    </g>
</svg>
@endsection

@section('message', 'Waktu Permintaan Habis')

@section('description')
{{ config('app.debug') && $exception->getMessage() ? $exception->getMessage() : 'Server kami membutuhkan waktu terlalu lama untuk merespons. Silakan coba lagi dalam beberapa saat.' }}
@endsection
