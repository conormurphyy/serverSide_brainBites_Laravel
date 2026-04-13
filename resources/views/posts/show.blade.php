@extends('layouts.site')

@section('title', 'BrainBites | '.$post->title)

@section('content')
    @php
        $sections = collect(preg_split('/\R{2,}/', trim($post->body)) ?: [])
            ->map(fn (string $chunk): string => trim($chunk))
            ->filter(fn (string $chunk): bool => $chunk !== '')
            ->values();

        $tocSections = $sections->take(8)->map(function (string $chunk, int $index): array {
            $label = \Illuminate\Support\Str::limit(trim(str_replace(["\r", "\n"], ' ', $chunk)), 58);

            return [
                'id' => 'section-'.($index + 1),
                'label' => $label === '' ? 'Section '.($index + 1) : $label,
            ];
        });
    @endphp

    <div
        data-recent-view-post
        data-title="{{ $post->title }}"
        data-url="{{ route('posts.show', $post) }}"
        data-category="{{ $post->category->name }}"
        data-category-slug="{{ $post->category->slug }}"
        hidden
    ></div>

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
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" id="readingModeToggle" aria-pressed="false" aria-label="Toggle reading mode">Reading mode</button>
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" id="voiceReadToggle" aria-label="Listen to this post">Listen</button>
                <button class="bb-button-secondary border-white/30 bg-white/10 text-white hover:bg-white/20" type="button" id="voiceReadStop" aria-label="Stop audio" disabled>Stop</button>
            </div>
            <p id="voiceReadStatus" class="mt-2 text-xs text-cyan-100/85" aria-live="polite">Voice reader ready.</p>
            <p class="mt-3 text-xs text-cyan-100/90">{{ $post->reading_time_minutes }} min read</p>
            <p class="mt-1 text-xs text-cyan-100/90">Difficulty: <span class="{{ $post->difficulty_badge_class }}">{{ $post->difficulty_level }}</span></p>
        </div>
    </section>

    <article id="postReadingLayout" class="mb-8 grid gap-8 lg:grid-cols-3">
        <div id="postPrimaryColumn" class="lg:col-span-2">
            <div class="mb-4 flex items-center gap-3">
                <span class="{{ $post->category_badge_class }}">{{ $post->category->name }}</span>
                @if ($post->user->username)
                    <a href="{{ route('users.show', ['user' => $post->user->username]) }}" class="text-xs font-semibold text-slate-700 transition hover:text-cyan-700">By {{ $post->user->name }}</a>
                @else
                    <span class="text-xs font-semibold text-slate-700">By {{ $post->user->name }}</span>
                @endif
                <span class="text-xs text-slate-600">{{ $post->reading_time_minutes }} min read</span>
                @auth
                    @if (! auth()->user()->isAdmin() && auth()->id() !== $post->user_id)
                        <form action="{{ route('users.follow', $post->user) }}" method="POST">
                            @csrf
                            <button type="submit" class="bb-button-secondary !px-2 !py-1 !text-xs">
                                {{ $isFollowingAuthor ? 'Unfollow' : 'Follow' }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>

            <img
                src="{{ $post->image_source }}"
                alt="{{ $post->title }}"
                class="mt-6 h-72 w-full rounded-2xl object-cover sm:h-96"
            >

            <div class="bb-post-body mt-6">
                <div id="postContent" class="prose max-w-none text-slate-800 prose-headings:text-slate-900 prose-a:text-cyan-700" data-post-title="{{ $post->title }}" data-post-category="{{ $post->category->name }}" data-post-category-slug="{{ $post->category->slug }}" data-post-url="{{ route('posts.show', $post) }}">
                    @foreach ($sections as $index => $chunk)
                        <p id="section-{{ $index + 1 }}" class="scroll-mt-24">{!! nl2br(e($chunk)) !!}</p>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $post->likes->count() }} likes</span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">{{ $post->comments->count() }} comments</span>
                <button type="button" class="bb-button-secondary" data-copy-url="{{ route('posts.show', $post) }}">Copy link</button>

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

        <aside id="postSidebar" class="space-y-4">
            @if ($tocSections->isNotEmpty())
                <div class="bb-card">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-lg font-bold text-slate-900">Table of Contents</h2>
                        <span class="text-xs font-semibold text-slate-500" data-toc-progress-label>Section 1 of {{ $tocSections->count() }}</span>
                    </div>
                    <div class="bb-toc-progress mt-3">
                        <div class="bb-toc-progress-bar" data-toc-progress-bar></div>
                    </div>
                    <nav class="mt-3 grid gap-1">
                        @foreach ($tocSections as $toc)
                            <a href="#{{ $toc['id'] }}" class="bb-toc-link">{{ $toc['label'] }}</a>
                        @endforeach
                    </nav>
                </div>
            @endif

            <div class="bb-card">
                <h2 class="text-lg font-bold text-slate-900">Post details</h2>
                <p class="mt-2 text-sm text-slate-700">Published: {{ optional($post->published_at)->format('M d, Y') ?? 'Draft' }}</p>
                @if ($post->published_at && $post->published_at->isFuture())
                    <p class="mt-1 text-sm text-amber-700">Scheduled: {{ $post->published_at->format('M d, Y h:i A') }}</p>
                @endif
                <p class="mt-1 text-sm text-slate-700">Visibility: {{ $post->is_public ? 'Public' : 'Private draft' }}</p>
                <p class="mt-1 text-sm text-slate-700">Category: {{ $post->category->name }}</p>
                <p class="mt-1 text-sm text-slate-700">Estimated read: {{ $post->reading_time_minutes }} minutes</p>
                <p class="mt-1 text-sm text-slate-700">Complexity: <span class="{{ $post->difficulty_badge_class }}">{{ $post->difficulty_level }}</span></p>
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

    <section class="bb-card mb-8" id="comments-section" data-comments-sort="{{ $commentsSort }}">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Comments</h2>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700" data-comments-total>{{ $post->comments->count() }} total</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('posts.show', ['post' => $post, 'comments_sort' => 'top']) }}" class="bb-button-secondary !px-3 !py-1.5 !text-xs {{ $commentsSort === 'top' ? 'bb-comments-sort-active' : '' }}">Top</a>
                <a href="{{ route('posts.show', ['post' => $post, 'comments_sort' => 'new']) }}" class="bb-button-secondary !px-3 !py-1.5 !text-xs {{ $commentsSort === 'new' ? 'bb-comments-sort-active' : '' }}">New</a>
            </div>
        </div>

        @auth
            <form action="{{ route('comments.store', $post) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-3" data-comment-form>
                @csrf
                <div>
                    <label for="commentBody" class="bb-label">Add a comment</label>
                    <textarea id="commentBody" name="body" rows="4" class="bb-textarea" maxlength="1000" placeholder="Share a thought, add context, or ask a follow-up.">{{ old('body') }}</textarea>
                    @error('body')<p class="bb-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="commentImage" class="bb-label">Attach image (optional)</label>
                    <input id="commentImage" type="file" name="image" accept="image/*" class="bb-file-input">
                    @error('image')<p class="bb-error">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3" data-comment-voice>
                    <p class="bb-label">Voice note (optional, up to 30 seconds)</p>
                    <input type="file" name="voice_note" accept="audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/wav,audio/x-wav,audio/aac" class="sr-only" data-comment-voice-input>
                    <input type="hidden" name="voice_note_duration" value="" data-comment-voice-duration>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <button type="button" class="bb-button-secondary !px-3 !py-1.5 !text-xs" data-comment-voice-start>Record</button>
                        <button type="button" class="bb-button-secondary !px-3 !py-1.5 !text-xs" data-comment-voice-stop disabled>Stop</button>
                        <button type="button" class="bb-button-secondary !px-3 !py-1.5 !text-xs" data-comment-voice-clear disabled>Clear</button>
                    </div>
                    <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3" data-comment-voice-preview hidden>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Preview before posting</p>
                        <audio controls preload="metadata" class="mt-2 w-full" data-comment-voice-audio></audio>
                    </div>
                    <p class="mt-2 text-xs text-slate-600" data-comment-voice-status>No voice note recorded yet.</p>
                    @error('voice_note')<p class="bb-error">{{ $message }}</p>@enderror
                    @error('voice_note_duration')<p class="bb-error">{{ $message }}</p>@enderror
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

        <div class="mt-5 space-y-4" data-comments-root-list>
            @forelse ($rootComments as $comment)
                @include('posts.partials.comment', ['post' => $post, 'comment' => $comment, 'depth' => 0])
            @empty
                <p class="text-sm text-slate-700">No comments yet. Start the conversation.</p>
            @endforelse
        </div>
    </section>

    <section class="bb-card mb-8" id="postChatPanel">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Ask Brain Bot about this post</h2>
                <p class="mt-1 text-sm text-slate-600">Ask targeted follow-ups with this post as context.</p>
            </div>
        </div>
        <form id="postChatForm" class="mt-4 grid gap-3">
            <label for="postChatInput" class="bb-label">Your question</label>
            <input id="postChatInput" type="text" maxlength="500" class="bb-input" placeholder="Ask about this post..." required>
            <div>
                <button type="submit" class="bb-button-secondary">Ask Brain Bot</button>
            </div>
        </form>
        <div id="postChatAnswer" class="bb-inline-answer mt-3" hidden></div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.MediaRecorder || !navigator.mediaDevices?.getUserMedia) {
                return;
            }

            document.querySelectorAll('[data-comment-voice]').forEach((scope) => {
                const startButton = scope.querySelector('[data-comment-voice-start]');
                const stopButton = scope.querySelector('[data-comment-voice-stop]');
                const clearButton = scope.querySelector('[data-comment-voice-clear]');
                const status = scope.querySelector('[data-comment-voice-status]');
                const fileInput = scope.querySelector('[data-comment-voice-input]');
                const durationInput = scope.querySelector('[data-comment-voice-duration]');
                const preview = scope.querySelector('[data-comment-voice-preview]');
                const previewAudio = scope.querySelector('[data-comment-voice-audio]');

                if (!startButton || !stopButton || !clearButton || !status || !fileInput || !durationInput || !preview || !previewAudio) {
                    return;
                }

                let recorder = null;
                let chunks = [];
                let stream = null;
                let startedAt = 0;
                let autoStopTimer = 0;
                let previewUrl = '';

                const setIdle = () => {
                    startButton.disabled = false;
                    stopButton.disabled = true;
                };

                const resetVoice = () => {
                    if (previewUrl) {
                        window.URL.revokeObjectURL(previewUrl);
                        previewUrl = '';
                    }

                    const transfer = new DataTransfer();
                    fileInput.files = transfer.files;
                    durationInput.value = '';
                    clearButton.disabled = true;
                    preview.hidden = true;
                    previewAudio.removeAttribute('src');
                    previewAudio.load();
                    status.textContent = 'No voice note recorded yet.';
                };

                const stopTracks = () => {
                    if (!stream) {
                        return;
                    }

                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                };

                startButton.addEventListener('click', async () => {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    } catch {
                        status.textContent = 'Microphone permission denied or unavailable.';
                        return;
                    }

                    chunks = [];
                    recorder = new MediaRecorder(stream);
                    startedAt = Date.now();

                    recorder.ondataavailable = (event) => {
                        if (event.data && event.data.size > 0) {
                            chunks.push(event.data);
                        }
                    };

                    recorder.onstop = () => {
                        window.clearTimeout(autoStopTimer);

                        const elapsedSeconds = Math.min(30, Math.max(0.1, (Date.now() - startedAt) / 1000));
                        const mimeType = recorder?.mimeType || 'audio/webm';
                        const blob = new Blob(chunks, { type: mimeType });

                        if (blob.size > 0) {
                            const extension = mimeType.includes('ogg') ? 'ogg' : mimeType.includes('mpeg') ? 'mp3' : mimeType.includes('wav') ? 'wav' : mimeType.includes('mp4') ? 'm4a' : 'webm';
                            const file = new File([blob], `comment-voice-note.${extension}`, { type: mimeType });
                            const transfer = new DataTransfer();
                            transfer.items.add(file);
                            fileInput.files = transfer.files;
                            durationInput.value = elapsedSeconds.toFixed(1);
                            clearButton.disabled = false;
                            if (previewUrl) {
                                window.URL.revokeObjectURL(previewUrl);
                            }
                            previewUrl = window.URL.createObjectURL(blob);
                            previewAudio.src = previewUrl;
                            preview.hidden = false;
                            status.textContent = `Voice note ready (${elapsedSeconds.toFixed(1)}s).`;
                        } else {
                            resetVoice();
                        }

                        recorder = null;
                        chunks = [];
                        stopTracks();
                        setIdle();
                    };

                    recorder.start();
                    startButton.disabled = true;
                    stopButton.disabled = false;
                    status.textContent = 'Recording... it will stop at 30 seconds.';

                    autoStopTimer = window.setTimeout(() => {
                        if (recorder && recorder.state !== 'inactive') {
                            recorder.stop();
                        }
                    }, 30000);
                });

                stopButton.addEventListener('click', () => {
                    if (recorder && recorder.state !== 'inactive') {
                        recorder.stop();
                    }
                });

                clearButton.addEventListener('click', () => {
                    if (recorder && recorder.state !== 'inactive') {
                        recorder.stop();
                    }
                    resetVoice();
                });

                setIdle();
            });
        });
    </script>

    <section class="bb-card mb-8" id="flashcardsPanel">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Study Flashcards from Post</h2>
                <p class="mt-1 text-sm text-slate-600">Open an interactive flashcard modal with swipe and flip support.</p>
            </div>
            <button type="button" class="bb-button-secondary" id="openFlashcardsModal">Open flashcards</button>
        </div>
        <p class="mt-3 text-sm text-slate-600">Tip: tap card to reveal answer, swipe left/right to move, or use Prev/Next.</p>
    </section>

    <div id="flashcardsModal" class="bb-modal bb-flashcard-modal" hidden>
        <div class="bb-modal-backdrop" data-flashcards-close></div>
        <div class="bb-modal-panel" role="dialog" tabindex="-1" aria-modal="true" aria-labelledby="flashcardsModalTitle">
            <div class="flex items-center justify-between gap-2">
                <h2 id="flashcardsModalTitle" class="text-xl font-bold text-slate-900">Interactive Flashcards</h2>
                <button type="button" class="bb-button-secondary" data-flashcards-close>Close</button>
            </div>
            <p class="mt-2 text-sm text-slate-600">Generate or resume your category deck below.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" class="bb-button-secondary" id="generateFlashcards">Generate cards</button>
                <button type="button" class="bb-button-secondary" id="saveFlashcards" disabled>Save deck</button>
            </div>
            <div id="flashcardsDeck" class="mt-4"></div>
        </div>
    </div>

    <section class="bb-card mb-8" id="revisionPanel">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">One-click Revision Mode</h2>
                <p class="mt-1 text-sm text-slate-600">Convert this post into bullets, exam questions, or a cheat sheet.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="bb-button-secondary" data-revision-mode="bullets">Bullets</button>
                <button type="button" class="bb-button-secondary" data-revision-mode="questions">Exam questions</button>
                <button type="button" class="bb-button-secondary" data-revision-mode="cheat">Cheat sheet</button>
            </div>
        </div>
        <div id="revisionOutput" class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
            Pick a mode to generate a revision view.
        </div>
    </section>

    <section class="bb-card" id="postFeedbackPanel" data-feedback-key="post-feedback-{{ $post->id }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Was this post helpful?</h2>
                <p class="mt-1 text-sm text-slate-600">Your quick feedback helps improve what shows up first.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="bb-button-secondary" data-feedback="yes" aria-label="This post was helpful">Yes</button>
                <button type="button" class="bb-button-secondary" data-feedback="no" aria-label="This post was not helpful">No</button>
            </div>
        </div>
        <p id="postFeedbackStatus" class="mt-3 text-sm text-slate-600" aria-live="polite">No feedback submitted yet.</p>
    </section>

@endsection
