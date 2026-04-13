<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'BrainBites'))</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
        <link rel="shortcut icon" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <meta name="theme-color" content="#0a1f36">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="BrainBites">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|instrument-serif:400" rel="stylesheet" />
        @include('partials.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        {{-- Hack to prevent Tailwind from purging dark modifiers --}}
        <div class="hidden dark theme-dark html.dark html.dark body" aria-hidden="true"></div>

        <div class="bb-atmosphere" aria-hidden="true"></div>

        <header class="bb-nav">
            <div class="bb-shell flex min-h-16 items-center justify-between gap-4 py-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-900">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-cyan-400 shadow-[0_0_16px_rgba(72,242,255,0.9)]"></span>
                    Brain<span class="text-cyan-700">Bites</span>
                </a>

                <button
                    type="button"
                    id="mobileNavToggle"
                    class="bb-button-secondary px-3 py-2 text-xs md:hidden"
                    aria-expanded="false"
                    aria-controls="mobileNavPanel"
                >
                    Menu
                </button>

                <nav class="hidden items-center gap-2 text-sm font-medium text-slate-700 md:flex">
                    <button type="button" data-theme-toggle class="bb-button-secondary">Dark mode</button>
                    <a href="{{ route('posts.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('home') || request()->routeIs('posts.*') ? 'bb-nav-active' : '' }}">Explore</a>
                    @auth
                        @if (! auth()->user()->isAdmin())
                            <a href="{{ route('following.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('following.*') ? 'bb-nav-active' : '' }}">Following</a>
                            <a href="{{ route('bookmarks.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('bookmarks.*') ? 'bb-nav-active' : '' }}">Bookmarks</a>
                        @endif
                    @endauth
                    <a href="{{ route('brainbot.page') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('brainbot.*') ? 'bb-nav-active' : '' }}">brainBot</a>
                    <a href="{{ route('glossary.page') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('glossary.*') ? 'bb-nav-active' : '' }}">Glossary</a>
                    <a href="{{ route('about') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('about') ? 'bb-nav-active' : '' }}">About</a>
                    <a href="{{ route('contact') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('contact') || request()->routeIs('contact.submit') ? 'bb-nav-active' : '' }}">Contact</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('dashboard') ? 'bb-nav-active' : '' }}">Dashboard</a>
                        <a href="{{ route('posts.create') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('posts.create') ? 'bb-nav-active' : '' }}">Add Post</a>

                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.contact-messages.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('admin.contact-messages.*') ? 'bb-nav-active' : '' }}">Inbox</a>
                        @else
                            <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('profile.*') ? 'bb-nav-active' : '' }}" aria-label="Profile">
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover border border-cyan-200 shadow-sm {{ request()->routeIs('profile.*') ? 'ring-2 ring-cyan-400' : '' }}">
                            </a>
                        @endif

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

            <div id="mobileNavPanel" class="bb-shell hidden pb-4 md:hidden">
                <nav class="grid gap-2 text-sm font-medium text-slate-700">
                    <button type="button" data-theme-toggle class="bb-button-secondary justify-start">Dark mode</button>
                    <a href="{{ route('posts.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('home') || request()->routeIs('posts.*') ? 'bb-nav-active' : '' }}">Explore</a>
                    @auth
                        @if (! auth()->user()->isAdmin())
                            <a href="{{ route('following.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('following.*') ? 'bb-nav-active' : '' }}">Following</a>
                            <a href="{{ route('bookmarks.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('bookmarks.*') ? 'bb-nav-active' : '' }}">Bookmarks</a>
                        @endif
                    @endauth
                    <a href="{{ route('brainbot.page') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('brainbot.*') ? 'bb-nav-active' : '' }}">brainBot</a>
                    <a href="{{ route('glossary.page') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('glossary.*') ? 'bb-nav-active' : '' }}">Glossary</a>
                    <a href="{{ route('about') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('about') ? 'bb-nav-active' : '' }}">About</a>
                    <a href="{{ route('contact') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('contact') || request()->routeIs('contact.submit') ? 'bb-nav-active' : '' }}">Contact</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('dashboard') ? 'bb-nav-active' : '' }}">Dashboard</a>
                        <a href="{{ route('posts.create') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('posts.create') ? 'bb-nav-active' : '' }}">Add Post</a>

                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.contact-messages.index') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('admin.contact-messages.*') ? 'bb-nav-active' : '' }}">Inbox</a>
                        @else
                            <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70 {{ request()->routeIs('profile.*') ? 'bb-nav-active' : '' }}">Profile</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bb-button w-full">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 transition hover:bg-white/70">Log in</a>
                        <a href="{{ route('register') }}" class="bb-button">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="bb-shell py-8 sm:py-10 lg:py-12">
            @unless (request()->routeIs('home') || request()->routeIs('posts.index'))
                <div class="mb-5">
                    <button type="button" class="bb-back-nav" data-back-nav data-fallback-url="{{ route('home') }}" aria-label="Go back to previous page">
                        <span aria-hidden="true">&larr;</span>
                        Back
                    </button>
                </div>
            @endunless

            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bb-footer mt-8">
            <div class="bb-shell py-10">
                <div class="bb-footer-grid">
                    <div>
                        <p class="bb-kicker">Stay Curious</p>
                        <h2 class="bb-title-font mt-3 text-3xl text-white sm:text-4xl">BrainBites</h2>
                        <p class="mt-3 max-w-md text-sm text-cyan-100/90">
                            Bite-sized ideas, visual explanations, and people you can follow to keep learning momentum high.
                        </p>
                    </div>

                    <nav aria-label="Footer quick links">
                        <p class="bb-footer-heading">Explore</p>
                        <div class="bb-footer-links">
                            <a href="{{ route('posts.index') }}">Explore Posts</a>
                            <a href="{{ route('brainbot.page') }}">brainBot</a>
                            <a href="{{ route('glossary.page') }}">Glossary</a>
                            <a href="{{ route('about') }}">About</a>
                            <a href="{{ route('contact') }}">Contact</a>
                        </div>
                    </nav>

                    <nav aria-label="Footer account links">
                        <p class="bb-footer-heading">Account</p>
                        <div class="bb-footer-links">
                            @auth
                                @if (! auth()->user()->isAdmin())
                                    <a href="{{ route('following.index') }}">Following Feed</a>
                                    <a href="{{ route('bookmarks.index') }}">Bookmarks</a>
                                @else
                                    <a href="{{ route('admin.contact-messages.index') }}">Admin Inbox</a>
                                @endif
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                                <a href="{{ route('posts.create') }}">Create Post</a>
                            @else
                                <a href="{{ route('login') }}">Log in</a>
                                <a href="{{ route('register') }}">Register</a>
                            @endauth
                        </div>
                    </nav>
                </div>

                <div class="bb-footer-bottom">
                    <p>Copyright {{ now()->year }} BrainBites. Keep asking better questions.</p>
                    <a href="{{ route('home') }}">Back to Home</a>
                </div>
            </div>
        </footer>

        <div id="deleteModal" class="bb-modal" hidden>
            <div class="bb-modal-backdrop" data-delete-close></div>
            <div class="bb-modal-panel" role="dialog" tabindex="-1" aria-modal="true" aria-labelledby="deleteModalTitle" aria-describedby="deleteModalText">
                <h2 id="deleteModalTitle" class="text-xl font-bold text-slate-900">Delete Post?</h2>
                <p id="deleteModalText" class="mt-2 text-sm text-slate-600">This action cannot be undone.</p>
                <div class="mt-5 flex gap-2">
                    <button type="button" class="bb-button-secondary" data-delete-close>Cancel</button>
                    <button type="button" class="bb-button" id="deleteModalConfirm">Yes, delete it</button>
                </div>
            </div>
        </div>

        <div id="bbToast" class="bb-toast" role="status" aria-live="polite" hidden></div>
        <button id="backToTop" type="button" class="bb-backtotop" aria-label="Back to top" hidden>Top</button>
        @include('partials.voice-to-text')
    </body>
</html>
