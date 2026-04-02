@extends('layouts.site')

@section('title', 'BrainBites | Curiosity, explained')

@section('content')
    <section class="bb-hero-grid mb-8">
        <div class="bb-hero-content">
            <p class="bb-kicker">Curiosity Explorer</p>
            <h1 class="bb-title-font text-4xl leading-tight text-white sm:text-5xl lg:text-6xl">
                Not another wall of text.
                <span class="block text-cyan-200">A visual map of what people are actually asking.</span>
            </h1>

            <p class="mt-5 max-w-xl text-sm text-cyan-50/90 sm:text-base">
                The interactive topic map is driven by your real categories and live post volume.
                Hover nodes to inspect topics and click any orbit to jump straight into that stream of questions.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#topic-universe" class="bb-button">Explore Topic Universe</a>
                @guest
                    <a href="{{ route('register') }}" class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20">Start Contributing</a>
                @else
                    <a href="{{ route('posts.create') }}" class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20">Create Your Post</a>
                @endguest
            </div>
        </div>

        <div class="bb-topic-map" id="topic-universe" data-topic-map-wrapper>
            @if ($categoryMapData->isNotEmpty())
                <canvas id="topic-map-canvas" class="bb-topic-map-canvas" data-topic-map-canvas aria-hidden="true"></canvas>

                <div class="bb-topic-hud">
                    <p id="topicMapTitle" class="text-sm font-bold text-white">Hover a topic node</p>
                    <p id="topicMapMeta" class="mt-1 text-xs text-cyan-100/90">See post volume and latest question.</p>
                    <p id="topicMapHint" class="mt-2 text-[11px] uppercase tracking-wider text-cyan-200/80">Tip: click a node to filter</p>
                </div>

                <div class="bb-topic-legend">
                    @foreach ($categoryMapData as $item)
                        <a
                            href="{{ route('posts.index', ['category' => $item['slug']]) }}"
                            class="bb-topic-pill"
                            data-map-category-slug="{{ $item['slug'] }}"
                        >
                            {{ $item['name'] }} <span class="opacity-70">({{ $item['count'] }})</span>
                        </a>
                    @endforeach
                </div>

                <script type="application/json" id="topic-map-data">{!! json_encode($categoryMapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
            @else
                <div class="bb-topic-empty">
                    <p class="text-sm font-semibold text-white">Topic map unlocks when categories have public posts.</p>
                    <p class="mt-2 text-xs text-cyan-100/80">Publish your first post to power this visualization.</p>
                </div>
            @endif
        </div>
    </section>

    @php
        $orbitImages = $posts->getCollection()->take(6);
    @endphp

    @if ($orbitImages->isNotEmpty())
        <section class="bb-image-mosaic mb-10">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">Visual Orbit</h2>
                <p class="text-sm font-medium text-slate-600">Swipe your eyes across the universe of ideas</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($orbitImages as $imagePost)
                    <a href="{{ route('posts.show', $imagePost) }}" class="bb-mosaic-tile" data-tilt-card>
                        <div data-tilt-glare class="bb-post-glare"></div>
                        <img
                            src="{{ $imagePost->image_source }}"
                            alt="{{ $imagePost->title }}"
                            class="h-52 w-full rounded-xl object-cover"
                        >
                        <div class="mt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">{{ $imagePost->category->name }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $imagePost->title }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bb-glass bb-search-panel">
        <form action="{{ route('posts.index') }}" method="GET" class="grid gap-4 md:grid-cols-4 md:items-end">
            <div class="md:col-span-2">
                <label for="search" class="bb-label">Search Questions</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    class="bb-input"
                    value="{{ $search }}"
                    placeholder="Try: Why do cats purr?"
                >
            </div>

            <div>
                <label for="category" class="bb-label">Category</label>
                <select id="category" name="category" class="bb-select">
                    <option value="">All categories</option>
                    @foreach ($categories as $item)
                        <option value="{{ $item->slug }}" @selected($selectedCategory === $item->slug)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sort" class="bb-label">Sort</label>
                <select id="sort" name="sort" class="bb-select">
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="popular" @selected($sort === 'popular')>Most liked</option>
                    <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                </select>
            </div>

            <div class="md:col-span-4 flex flex-wrap gap-2">
                <button class="bb-button" type="submit">Apply</button>
                <a class="bb-button-secondary" href="{{ route('posts.index') }}">Reset</a>
            </div>
        </form>
    </section>

    @if ($featuredPosts->isNotEmpty())
        <section class="mb-10">
            <h2 class="mb-4 text-xl font-bold text-slate-900">Featured Visual Stories</h2>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($featuredPosts as $featured)
                    <article class="bb-feature-card" data-tilt-card>
                        <div data-tilt-glare class="bb-post-glare"></div>
                        <img
                            src="{{ $featured->image_source }}"
                            alt="{{ $featured->title }}"
                            class="mb-4 h-40 w-full rounded-xl object-cover"
                        >

                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-cyan-200">{{ $featured->category->name }}</p>
                        <h3 class="text-lg font-bold text-white">{{ $featured->title }}</h3>
                        <p class="mt-2 text-sm text-cyan-50/85">{{ $featured->summary }}</p>
                        <div class="mt-4 flex items-center justify-between text-xs text-cyan-100/75">
                            <span>{{ $featured->likes_count }} likes</span>
                            <a href="{{ route('posts.show', $featured) }}" class="font-semibold text-lime-200">Read</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mb-10 grid gap-5 lg:grid-cols-4">
        <article class="bb-model-card">
            <p class="text-xs uppercase tracking-[0.15em] text-cyan-700">Public Posts</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($communityStats['public_posts']) }}</p>
            <p class="mt-2 text-sm text-slate-600">Curiosity capsules available to explore.</p>
        </article>
        <article class="bb-model-card">
            <p class="text-xs uppercase tracking-[0.15em] text-cyan-700">Total Likes</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($communityStats['public_likes']) }}</p>
            <p class="mt-2 text-sm text-slate-600">Signals of what helped people most.</p>
        </article>
        <article class="bb-model-card">
            <p class="text-xs uppercase tracking-[0.15em] text-cyan-700">Active Categories</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($communityStats['active_categories']) }}</p>
            <p class="mt-2 text-sm text-slate-600">Topic clusters with live momentum.</p>
        </article>
        <article class="bb-model-card">
            <p class="text-xs uppercase tracking-[0.15em] text-cyan-700">Contributors</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($communityStats['contributors']) }}</p>
            <p class="mt-2 text-sm text-slate-600">People sharing ideas and answers.</p>
        </article>
    </section>

    <section class="mb-10 grid gap-5 lg:grid-cols-2">
        <article class="bb-card">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">Top Contributors</h2>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Community Leaders</span>
            </div>

            @if ($topContributors->isEmpty())
                <p class="mt-4 text-sm text-slate-600">No contributor data yet.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($topContributors as $contributor)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $contributor->name }}</p>
                                <p class="text-xs text-slate-500">{{ $contributor->public_posts_count }} public posts</p>
                            </div>
                            <span class="bb-chip">{{ $contributor->likes_count }} likes</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="bb-card">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">Fresh Picks</h2>
                <a href="#latest" class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Jump to Feed</a>
            </div>

            @if ($freshPicks->isEmpty())
                <p class="mt-4 text-sm text-slate-600">No fresh picks yet.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($freshPicks as $pick)
                        <a href="{{ route('posts.show', $pick) }}" class="block rounded-xl border border-slate-200/80 bg-white p-3 transition hover:border-cyan-300">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ $pick->category->name }}</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $pick->title }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $pick->summary }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

    <section id="latest">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Latest Question Capsules</h2>
            @auth
                <a href="{{ route('posts.create') }}" class="bb-button">Create Post</a>
            @endauth
        </div>

        @if ($posts->isEmpty())
            <div class="bb-card">
                <p class="text-slate-600">No posts match your search yet. Try another keyword or category.</p>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="bb-post-card flex flex-col" data-tilt-card>
                    <div data-tilt-glare class="bb-post-glare"></div>

                    <img
                        src="{{ $post->image_source }}"
                        alt="{{ $post->title }}"
                        class="h-44 w-full rounded-xl object-cover"
                    >

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span class="bb-chip">{{ $post->category->name }}</span>
                        @if (! $post->is_public)
                            <span class="rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-800">Draft</span>
                        @endif
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $post->title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $post->summary }}</p>

                    <div class="mt-5 flex items-center justify-between text-xs text-slate-500">
                        <span>By {{ $post->user->name }}</span>
                        <span>{{ $post->likes_count }} likes</span>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">View</a>

                        @auth
                            <form action="{{ route('posts.like', $post) }}" method="POST">
                                @csrf
                                <button type="submit" class="bb-button-secondary">
                                    {{ $post->isLikedBy(auth()->user()) ? 'Unlike' : 'Like' }}
                                </button>
                            </form>
                        @endauth
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
