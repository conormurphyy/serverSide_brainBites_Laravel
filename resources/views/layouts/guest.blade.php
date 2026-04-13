<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BrainBites') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|instrument-serif:400" rel="stylesheet" />
        @include('partials.theme-init')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-900 dark:text-slate-100">
        <div class="bb-atmosphere" aria-hidden="true"></div>

        <div class="bb-shell flex min-h-screen flex-col items-center justify-center py-10">
            <div class="mb-4 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-cyan-400 shadow-[0_0_16px_rgba(72,242,255,0.9)]"></span>
                    Brain<span class="text-cyan-700">Bites</span>
                </a>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Sign in to your curiosity dashboard.</p>
            </div>

            <div class="w-full max-w-md rounded-2xl border border-white/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/70 p-6 shadow-xl shadow-cyan-900/10 backdrop-blur">
                {{ $slot }}
            </div>
        </div>
        @include('partials.voice-to-text')
    </body>
</html>
