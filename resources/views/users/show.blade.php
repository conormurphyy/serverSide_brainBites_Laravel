@extends('layouts.site')

@section('title', 'BrainBites | '.$profileUser->name)

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div class="flex items-start gap-4">
            <img src="{{ $profileUser->profile_photo_url }}" alt="{{ $profileUser->name }}" class="h-20 w-20 rounded-full border border-cyan-200/60 object-cover shadow-lg">
            <div>
                <p class="bb-kicker">Creator Profile</p>
                <h1 class="bb-title-font mt-2 text-4xl text-white sm:text-5xl">{{ $profileUser->name }}</h1>
                <p class="mt-2 text-sm font-semibold text-cyan-200">{{ '@'.$profileUser->username }}</p>
                <p class="mt-3 max-w-2xl text-sm text-cyan-100/90 sm:text-base">
                    {{ $profileUser->bio ?: 'This creator has not added a bio yet.' }}
                </p>
                @auth
                    @if (! auth()->user()->isAdmin() && auth()->id() !== $profileUser->id)
                        <form action="{{ route('users.follow', $profileUser) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20">
                                {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <article class="bb-focus-card text-cyan-50">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Followers</p>
                <p class="mt-1 text-3xl font-bold">{{ number_format($stats['followers']) }}</p>
            </article>
            <article class="bb-focus-card text-cyan-50">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Following</p>
                <p class="mt-1 text-3xl font-bold">{{ number_format($stats['following']) }}</p>
            </article>
            <article class="bb-focus-card text-cyan-50">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Public Posts</p>
                <p class="mt-1 text-3xl font-bold">{{ number_format($stats['public_posts']) }}</p>
            </article>
            <article class="bb-focus-card text-cyan-50">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Total Likes</p>
                <p class="mt-1 text-3xl font-bold">{{ number_format($stats['total_likes']) }}</p>
            </article>
        </div>
    </section>

    <section class="mb-8">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Top Posts</h2>
            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Most Liked</span>
        </div>

        @if ($topPosts->isEmpty())
            <div class="bb-card">
                <p class="text-sm text-slate-600">No public posts yet.</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($topPosts as $post)
                    <article class="bb-post-card flex flex-col" data-tilt-card>
                        <div data-tilt-glare class="bb-post-glare"></div>
                        <img src="{{ $post->image_source }}" alt="{{ $post->title }}" class="h-44 w-full rounded-xl object-cover">
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                            <span class="{{ $post->category_badge_class }}">{{ $post->category->name }}</span>
                            <span>{{ $post->likes_count }} likes</span>
                        </div>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">{{ $post->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $post->summary }}</p>
                        <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary mt-4">Read Post</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Recent Posts</h2>
            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Latest Activity</span>
        </div>

        @if ($recentPosts->isEmpty())
            <div class="bb-card">
                <p class="text-sm text-slate-600">No recent public posts yet.</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($recentPosts as $post)
                    <article class="bb-post-card flex flex-col" data-tilt-card>
                        <div data-tilt-glare class="bb-post-glare"></div>
                        <img src="{{ $post->image_source }}" alt="{{ $post->title }}" class="h-44 w-full rounded-xl object-cover">
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                            <span class="{{ $post->category_badge_class }}">{{ $post->category->name }}</span>
                            <span>{{ $post->comments_count }} comments</span>
                        </div>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">{{ $post->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $post->summary }}</p>
                        <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary mt-4">Read Post</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
