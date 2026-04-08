<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'BrainBites'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|instrument-serif:400" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="bb-atmosphere" aria-hidden="true"></div>

        <header class="bb-nav">
            <div class="bb-shell flex h-16 items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-900">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-cyan-400 shadow-[0_0_16px_rgba(72,242,255,0.9)]"></span>
                    Brain<span class="text-cyan-700">Bites</span>
                </a>

                <nav class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <button type="button" id="themeToggle" class="bb-button-secondary">Dark mode</button>
                    <a href="{{ route('posts.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Explore</a>
                    <a href="{{ route('brainbot.page') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">brainBot</a>
                    <a href="{{ route('about') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">About</a>
                    <a href="{{ route('contact') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Contact</a>

                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.contact-messages.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Inbox</a>
                        @endif
                        <a href="{{ route('bookmarks.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Bookmarks</a>
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Dashboard</a>
                        <a href="{{ route('posts.create') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Add Post</a>
                        <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70" aria-label="Profile">
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover border border-cyan-200 shadow-sm">
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bb-button">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Log in</a>
                        <a href="{{ route('register') }}" class="bb-button">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="bb-shell py-8 sm:py-10 lg:py-12">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

        <div id="deleteModal" class="bb-modal" hidden>
            <div class="bb-modal-backdrop" data-delete-close></div>
            <div class="bb-modal-panel" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" aria-describedby="deleteModalText">
                <h2 id="deleteModalTitle" class="text-xl font-bold text-slate-900">Delete Post?</h2>
                <p id="deleteModalText" class="mt-2 text-sm text-slate-600">This action cannot be undone.</p>
                <div class="mt-5 flex gap-2">
                    <button type="button" class="bb-button-secondary" data-delete-close>Cancel</button>
                    <button type="button" class="bb-button" id="deleteModalConfirm">Yes, delete it</button>
                </div>
            </div>
        </div>
    </body>
</html>
