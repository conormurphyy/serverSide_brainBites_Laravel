@extends('layouts.site')

@section('title', 'BrainBites | '.$post->title)

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Deep Dive</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">{{ $post->title }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">{{ $post->summary }}</p>
        </div>

        <div class="bb-focus-card" id="readingTools">
            <h2 class="text-lg font-bold text-white">Reading Tools</h2>
            <p class="mt-2 text-sm text-cyan-100/85">Tune readability instantly while you explore this answer.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" data-font-size="small">A-</button>
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" data-font-size="normal">A</button>
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" data-font-size="large">A+</button>
            </div>
        </div>
    </section>

    <article class="mb-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center gap-3">
                <span class="bb-chip">{{ $post->category->name }}</span>
                <span class="text-xs text-slate-500">By {{ $post->user->name }}</span>
            </div>

            <img
                src="{{ $post->image_source }}"
                alt="{{ $post->title }}"
                class="mt-6 h-72 w-full rounded-2xl object-cover sm:h-96"
            >

            <div id="postContent" class="prose mt-6 max-w-none text-slate-700 prose-headings:text-slate-900 prose-a:text-cyan-700">
                {!! nl2br(e($post->body)) !!}
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $post->likes->count() }} likes</span>

                @auth
                    <form action="{{ route('posts.like', $post) }}" method="POST">
                        @csrf
                        <button class="bb-button-secondary" type="submit">
                            {{ $post->isLikedBy(auth()->user()) ? 'Unlike' : 'Like this answer' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bb-button-secondary">Log in to like</a>
                @endauth

                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}" class="bb-button-secondary">Edit</a>

                    <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button class="bb-button-secondary" type="submit">Delete</button>
                    </form>
                @endcan
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bb-card">
                <h2 class="text-lg font-bold text-slate-900">Post details</h2>
                <p class="mt-2 text-sm text-slate-600">Published: {{ optional($post->published_at)->format('M d, Y') ?? 'Draft' }}</p>
                <p class="mt-1 text-sm text-slate-600">Visibility: {{ $post->is_public ? 'Public' : 'Private draft' }}</p>
                <p class="mt-1 text-sm text-slate-600">Category: {{ $post->category->name }}</p>
            </div>

            @if ($relatedPosts->isNotEmpty())
                <div class="bb-card">
                    <h2 class="text-lg font-bold text-slate-900">Related Questions</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($relatedPosts as $related)
                            <a href="{{ route('posts.show', $related) }}" class="block rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                                <img
                                    src="{{ $related->image_source }}"
                                    alt="{{ $related->title }}"
                                    class="mb-2 h-28 w-full rounded-lg object-cover"
                                >
                                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">{{ $related->category->name }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $related->title }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </article>
@endsection
