@extends('layouts.site')

@section('title', 'BrainBites | Following Feed')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Personal Stream</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Following Feed</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">
                Browse posts from creators you follow in one focused, scroll-friendly space.
            </p>
        </div>

        <div class="bb-focus-card">
            <h2 class="text-lg font-bold text-white">Your Network</h2>
            <p class="mt-2 text-sm text-cyan-100/85">Following {{ $followedUsers->count() }} creators.</p>
            <p class="mt-1 text-xs text-cyan-100/85">Use the left panel to quickly manage your follows.</p>
        </div>
    </section>

    <section class="mb-6 bb-glass bb-search-panel">
        <form action="{{ route('following.index') }}" method="GET" class="grid gap-4 md:grid-cols-2 md:items-end">
            <div>
                <label for="sort" class="bb-label">Sort</label>
                <select id="sort" name="sort" class="bb-select">
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="popular" @selected($sort === 'popular')>Most liked</option>
                    <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                </select>
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-2">
                <button class="bb-button" type="submit">Apply</button>
                <a href="{{ route('following.index') }}" class="bb-button-secondary">Reset</a>
                <a href="{{ route('posts.index') }}" class="bb-button-secondary">Explore All Posts</a>
            </div>
        </form>
    </section>

    <section class="grid gap-6 lg:grid-cols-4">
        <aside class="lg:col-span-1 space-y-5">
            <article class="bb-card">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">Following</h2>
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ $followedUsers->count() }}</span>
                </div>

                @if ($followedUsers->isEmpty())
                    <p class="mt-3 text-sm text-slate-600">You are not following anyone yet.</p>
                @else
                    <div class="mt-3 max-h-[26rem] space-y-2 overflow-y-auto pr-1">
                        @foreach ($followedUsers as $followed)
                            <div class="rounded-xl border border-slate-200/80 bg-white p-3">
                                <p class="font-semibold text-slate-900">{{ $followed->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $followed->public_posts_count }} posts • {{ $followed->followers_count }} followers</p>
                                <form action="{{ route('users.follow', $followed) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="bb-button-secondary w-full">Unfollow</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="bb-card">
                <h2 class="text-lg font-bold text-slate-900">Suggested Creators</h2>
                @if ($suggestedUsers->isEmpty())
                    <p class="mt-3 text-sm text-slate-600">No suggestions right now.</p>
                @else
                    <div class="mt-3 max-h-[26rem] space-y-2 overflow-y-auto pr-1">
                        @foreach ($suggestedUsers as $suggested)
                            <div class="rounded-xl border border-slate-200/80 bg-white p-3">
                                <p class="font-semibold text-slate-900">{{ $suggested->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $suggested->public_posts_count }} posts • {{ $suggested->followers_count }} followers</p>
                                <form action="{{ route('users.follow', $suggested) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="bb-button-secondary w-full">Follow</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        </aside>

        <div class="lg:col-span-3">
            @if ($posts->isEmpty())
                <div class="bb-card">
                    <p class="text-slate-600">No posts in your following feed yet.</p>
                    <p class="mt-2 text-sm text-slate-500">Follow more creators or try a different search.</p>
                    <a href="{{ route('posts.index') }}" class="bb-button mt-4 inline-flex">Find Creators</a>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <article class="bb-post-card flex flex-col" data-tilt-card>
                            <div data-tilt-glare class="bb-post-glare"></div>

                            <img
                                src="{{ $post->image_source }}"
                                alt="{{ $post->title }}"
                                class="h-44 w-full rounded-xl object-cover"
                            >

                            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                                <span class="{{ $post->category_badge_class }}">{{ $post->category->name }}</span>
                                <span>{{ $post->likes_count }} likes</span>
                            </div>

                            <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $post->title }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ $post->summary }}</p>

                            <div class="mt-5 flex items-center justify-between text-xs text-slate-500">
                                <a href="{{ route('users.show', ['user' => $post->user->username]) }}" class="font-semibold transition hover:text-cyan-700">By {{ $post->user->name }}</a>
                                <span>{{ $post->comments_count }} comments</span>
                            </div>

                            <div class="mt-2">
                                <span class="{{ $post->difficulty_badge_class }}">{{ $post->difficulty_level }}</span>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">View</a>
                                <button type="button" class="bb-button-secondary" data-copy-url="{{ route('posts.show', $post) }}">Copy link</button>
                                <form action="{{ route('posts.like', $post) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bb-button-secondary">
                                        {{ $post->isLikedBy(auth()->user()) ? 'Unlike' : 'Like' }}
                                    </button>
                                </form>
                                <form action="{{ route('posts.bookmark', $post) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bb-button-secondary">
                                        {{ $post->isBookmarkedBy(auth()->user()) ? 'Unsave' : 'Save' }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
