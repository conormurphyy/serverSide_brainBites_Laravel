@extends('layouts.site')

@section('title', 'BrainBites | '.$post->title)

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Deep Dive</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">{{ $post->title }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-50 sm:text-base">{{ $post->summary }}</p>
        </div>

        <div class="bb-focus-card" id="readingTools">
            <h2 class="text-lg font-bold text-white">Reading Tools</h2>
            <p class="mt-2 text-sm text-cyan-50">Tune readability instantly while you explore this answer.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" data-font-size="small" aria-pressed="false" aria-label="Set text size to small">A-</button>
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" data-font-size="normal" aria-pressed="true" aria-label="Set text size to normal">A</button>
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" data-font-size="large" aria-pressed="false" aria-label="Set text size to large">A+</button>
            </div>
        </div>
    </section>

    <article class="mb-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center gap-3">
                <span class="bb-chip">{{ $post->category->name }}</span>
                <span class="text-xs text-slate-700">By {{ $post->user->name }}</span>
            </div>

            <img
                src="{{ $post->image_source }}"
                alt="{{ $post->title }}"
                class="mt-6 h-72 w-full rounded-2xl object-cover sm:h-96"
            >

            <div class="bb-post-body mt-6">
                <div id="postContent" class="prose max-w-none text-slate-800 prose-headings:text-slate-900 prose-a:text-cyan-700">
                    {!! nl2br(e($post->body)) !!}
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $post->likes->count() }} likes</span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $post->comments->count() }} comments</span>

                @auth
                    @unless (auth()->user()->isAdmin())
                        <form action="{{ route('posts.like', $post) }}" method="POST">
                            @csrf
                            <button class="bb-button-secondary" type="submit">
                                {{ $post->isLikedBy(auth()->user()) ? 'Unlike' : 'Like this answer' }}
                            </button>
                        </form>
                    @else
                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-500">Likes disabled for admin accounts</span>
                    @endunless

                    @unless (auth()->user()->isAdmin())
                        <form action="{{ route('posts.bookmark', $post) }}" method="POST">
                            @csrf
                            <button class="bb-button-secondary" type="submit">
                                {{ $post->isBookmarkedBy(auth()->user()) ? 'Remove bookmark' : 'Save bookmark' }}
                            </button>
                        </form>
                    @else
                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-500">Bookmarks disabled for admin accounts</span>
                    @endunless
                @else
                    <a href="{{ route('login') }}" class="bb-button-secondary">Log in to like</a>
                @endauth

                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}" class="bb-button-secondary">Edit</a>

                    <form action="{{ route('posts.destroy', $post) }}" method="POST" data-delete-form="{{ $post->id }}">
                        @csrf
                        @method('DELETE')
                        <button
                            class="bb-button-secondary"
                            type="button"
                            data-delete-trigger
                            data-delete-form-id="{{ $post->id }}"
                            data-delete-title="{{ $post->title }}"
                        >
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bb-card">
                <h2 class="text-lg font-bold text-slate-900">Post details</h2>
                <p class="mt-2 text-sm text-slate-700">Published: {{ optional($post->published_at)->format('M d, Y') ?? 'Draft' }}</p>
                @if ($post->published_at && $post->published_at->isFuture())
                    <p class="mt-1 text-sm text-amber-700">Scheduled: {{ $post->published_at->format('M d, Y h:i A') }}</p>
                @endif
                <p class="mt-1 text-sm text-slate-700">Visibility: {{ $post->is_public ? 'Public' : 'Private draft' }}</p>
                <p class="mt-1 text-sm text-slate-700">Category: {{ $post->category->name }}</p>
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

            <section class="bb-card mt-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-slate-900">Comments</h2>
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ $post->comments->count() }} total</span>
                </div>

                @auth
                    <form action="{{ route('comments.store', $post) }}" method="POST" class="mt-4 grid gap-3">
                        @csrf
                        <div>
                            <label for="commentBody" class="bb-label">Add a comment</label>
                            <textarea id="commentBody" name="body" rows="4" class="bb-textarea" maxlength="1000" required placeholder="Share a thought, add context, or ask a follow-up.">{{ old('body') }}</textarea>
                            @error('body')<p class="bb-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <button type="submit" class="bb-button">Post Comment</button>
                        </div>
                    </form>
                @else
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <a href="{{ route('login') }}" class="font-semibold text-cyan-700">Log in</a> to join the discussion.
                    </div>
                @endauth

                <div class="mt-5 space-y-4">
                    @forelse ($post->comments->whereNull('parent_comment_id')->sortByDesc('created_at') as $comment)
                        @include('posts.partials.comment', ['post' => $post, 'comment' => $comment, 'depth' => 0])
                    @empty
                        <p class="text-sm text-slate-700">No comments yet. Start the conversation.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </article>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const content = document.getElementById('postContent');
            if (!content) return;

            const buttons = [...document.querySelectorAll('[data-font-size]')];
            if (!buttons.length) return;

            const storageKey = 'bb-reading-size';
            const presets = {
                small: { fontSize: '0.96rem', lineHeight: '1.7' },
                normal: { fontSize: '1.06rem', lineHeight: '1.85' },
                large: { fontSize: '1.22rem', lineHeight: '2' },
            };

            const apply = (size) => {
                const safeSize = Object.prototype.hasOwnProperty.call(presets, size) ? size : 'normal';
                const preset = presets[safeSize];

                content.style.fontSize = preset.fontSize;
                content.style.lineHeight = preset.lineHeight;

                buttons.forEach((button) => {
                    const active = button.dataset.fontSize === safeSize;
                    button.setAttribute('aria-pressed', String(active));
                    button.classList.toggle('ring-2', active);
                    button.classList.toggle('ring-cyan-300', active);
                });

                localStorage.setItem(storageKey, safeSize);
            };

            apply(localStorage.getItem(storageKey) || 'normal');

            buttons.forEach((button) => {
                if (button.dataset.readingBound === 'true') return;
                button.dataset.readingBound = 'true';

                button.addEventListener('click', () => {
                    apply(button.dataset.fontSize || 'normal');
                });
            });
        });
    </script>
@endsection
