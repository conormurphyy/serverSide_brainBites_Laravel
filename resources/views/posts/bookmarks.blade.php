@extends('layouts.site')

@section('title', 'BrainBites | Saved Bookmarks')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Read Later</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Saved Bookmarks</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">
                Your personal queue of answers worth revisiting.
            </p>
        </div>

        <div class="bb-focus-card">
            <h2 class="text-lg font-bold text-white">Tip</h2>
            <p class="mt-2 text-sm text-cyan-100/85">Use bookmarks to collect posts you want to study later.</p>
        </div>
    </section>

    @if ($bookmarkedPosts->isEmpty())
        <div class="bb-card">
            <p class="text-slate-600">No bookmarks yet. Explore posts and save the ones you like.</p>
            <p class="mt-2 text-sm text-slate-500">Tip: tap Save on any post card to build your study stack.</p>
            <a href="{{ route('posts.index') }}" class="bb-button mt-4 inline-flex">Explore Posts</a>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($bookmarkedPosts as $post)
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
                        <span>By {{ $post->user->name }}</span>
                        <span>{{ $post->reading_time_minutes }} min read</span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">View</a>
                        <button type="button" class="bb-button-secondary" data-copy-url="{{ route('posts.show', $post) }}">Copy link</button>

                        <form action="{{ route('posts.bookmark', $post) }}" method="POST">
                            @csrf
                            <button type="submit" class="bb-button-secondary">Remove bookmark</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $bookmarkedPosts->links() }}
        </div>
    @endif
@endsection
