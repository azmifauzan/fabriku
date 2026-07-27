@extends('errors.layout')

@section('title', 'Gateway Bermasalah')
@section('code', '502')

@section('illustration')
<svg class="w-full h-full" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="100" cy="100" r="80" class="stroke-rose-100 dark:stroke-rose-950/30" stroke-width="4" stroke-dasharray="6 6" />
    <circle cx="100" cy="100" r="70" class="fill-rose-50/20 dark:fill-rose-950/10" />

    <!-- Server Box Left -->
    <rect x="42" y="80" width="42" height="60" rx="6" class="fill-white dark:fill-slate-800 stroke-slate-300 dark:stroke-slate-700" stroke-width="3" />
    <circle cx="63" cy="94" r="4" class="fill-emerald-400" />
    <line x1="52" y1="108" x2="74" y2="108" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    <line x1="52" y1="118" x2="74" y2="118" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />

    <!-- Server Box Right -->
    <rect x="116" y="80" width="42" height="60" rx="6" class="fill-white dark:fill-slate-800 stroke-slate-300 dark:stroke-slate-700" stroke-width="3" />
    <circle cx="137" cy="94" r="4" class="fill-rose-500" />
    <line x1="126" y1="108" x2="148" y2="108" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />
    <line x1="126" y1="118" x2="148" y2="118" class="stroke-slate-200 dark:stroke-slate-700" stroke-width="3" stroke-linecap="round" />

    <!-- Broken connection -->
    <path d="M86 100H100" class="stroke-slate-300 dark:stroke-slate-600" stroke-width="4" stroke-linecap="round" stroke-dasharray="1 6" />
    <path d="M100 100H114" class="stroke-rose-400" stroke-width="4" stroke-linecap="round" stroke-dasharray="1 6" />

    <!-- X mark -->
    <g transform="translate(100, 62)">
        <circle cx="0" cy="0" r="20" class="fill-white dark:fill-slate-800 stroke-rose-500" stroke-width="4" />
        <path d="M-8 -8L8 8M8 -8L-8 8" class="stroke-rose-500" stroke-width="4" stroke-linecap="round" />
    </g>
</svg>
@endsection

@section('message', 'Gateway Bermasalah')

@section('description')
{{ config('app.debug') && $exception->getMessage() ? $exception->getMessage() : 'Server kami menerima respons yang tidak valid dari server lain. Tim teknis kami sedang menanganinya, silakan coba lagi sesaat lagi.' }}
@endsection
