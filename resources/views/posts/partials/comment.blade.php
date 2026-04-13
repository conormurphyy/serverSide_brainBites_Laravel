@php
    $indentClass = match ($depth ?? 0) {
        0 => 'ml-0',
        1 => 'ml-6',
        2 => 'ml-12',
        default => 'ml-12',
    };

    $sortedReplies = $comment->replies->sortBy('created_at')->values();
    $visibleReplies = $sortedReplies->take(2);
    $hiddenReplies = $sortedReplies->slice(2);
    $hiddenRepliesId = 'comment-hidden-replies-'.$comment->id;
    $upvotes = (int) ($comment->votes_count ?? 0);
@endphp

<article class="{{ $indentClass }} rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-comment-id="{{ $comment->id }}">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}" class="h-10 w-10 rounded-full border border-slate-200 object-cover">
            <div>
                <p class="font-semibold text-slate-900">{{ $comment->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $comment->created_at?->diffForHumans() }}</p>
            </div>
        </div>

        @auth
            @if (auth()->user()->isAdmin() || auth()->id() === $comment->user_id)
                <form action="{{ route('comments.destroy', [$post, $comment]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700">Delete</button>
                </form>
            @endif
        @endauth
    </div>

    @if (filled($comment->body))
        <p class="mt-3 whitespace-pre-wrap text-sm text-slate-700">{{ $comment->body }}</p>
    @endif

    @if ($comment->image_url)
        <div class="mt-3">
            <a href="{{ $comment->image_url }}" target="_blank" rel="noopener noreferrer" class="inline-block rounded-xl border border-slate-200 bg-slate-50 p-1">
                <img src="{{ $comment->image_url }}" alt="Comment image from {{ $comment->user->name }}" class="max-h-72 rounded-lg object-cover">
            </a>
        </div>
    @endif

    @if ($comment->voice_note_url)
        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Voice note{{ $comment->voice_note_duration ? ' ('.number_format($comment->voice_note_duration, 1).'s)' : '' }}</p>
            <audio controls preload="metadata" class="mt-2 w-full">
                <source src="{{ $comment->voice_note_url }}">
                Your browser does not support audio playback.
            </audio>
        </div>
    @endif

    <div class="mt-3 flex items-center gap-3">
        <span class="text-xs font-semibold text-slate-500" data-comment-upvote-count>{{ $upvotes }} {{ \Illuminate\Support\Str::plural('upvote', $upvotes) }}</span>

        @auth
            @unless (auth()->user()->isAdmin())
                <form action="{{ route('comments.upvote', [$post, $comment]) }}" method="POST" data-comment-upvote-form>
                    @csrf
                    <button type="submit" class="bb-button-secondary !px-3 !py-1.5 !text-xs {{ $comment->isUpvotedBy(auth()->user()) ? 'bb-comment-upvote-active' : '' }}" data-comment-upvote-button data-upvoted="{{ $comment->isUpvotedBy(auth()->user()) ? '1' : '0' }}">
                        {{ $comment->isUpvotedBy(auth()->user()) ? 'Upvoted' : 'Upvote helpful' }}
                    </button>
                </form>
            @else
                <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">Upvotes disabled for admin accounts</span>
            @endunless
        @endauth
    </div>

    @auth
        <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <summary class="cursor-pointer text-sm font-semibold text-cyan-700">Reply</summary>
            <form action="{{ route('comments.store', $post) }}" method="POST" enctype="multipart/form-data" class="mt-3 grid gap-3" data-comment-form>
                @csrf
                <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
                <div>
                    <label class="sr-only" for="replyBody-{{ $comment->id }}">Reply to {{ $comment->user->name }}</label>
                    <textarea id="replyBody-{{ $comment->id }}" name="body" rows="3" class="bb-textarea" maxlength="1000" placeholder="Write a reply to {{ $comment->user->name }}...">{{ old('body') }}</textarea>
                    @error('body')<p class="bb-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="bb-label" for="replyImage-{{ $comment->id }}">Attach image (optional)</label>
                    <input id="replyImage-{{ $comment->id }}" type="file" name="image" accept="image/*" class="bb-file-input">
                    @error('image')<p class="bb-error">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3" data-comment-voice>
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
                    <button type="submit" class="bb-button">Post Reply</button>
                </div>
            </form>
        </details>
    @endauth

    @if ($sortedReplies->isNotEmpty())
        <div class="mt-4 space-y-4 border-l-2 border-slate-200 pl-4" data-comment-replies-root>
            @foreach ($visibleReplies as $reply)
                @include('posts.partials.comment', ['post' => $post, 'comment' => $reply, 'depth' => ($depth ?? 0) + 1])
            @endforeach

            @if ($hiddenReplies->isNotEmpty())
                <button
                    type="button"
                    class="bb-button-secondary !px-3 !py-1.5 !text-xs"
                    data-replies-toggle
                    data-target="{{ $hiddenRepliesId }}"
                    data-expand-label="Show more replies ({{ $hiddenReplies->count() }})"
                    data-collapse-label="Show fewer replies"
                >
                    Show more replies ({{ $hiddenReplies->count() }})
                </button>

                <div id="{{ $hiddenRepliesId }}" class="space-y-4" hidden>
                    @foreach ($hiddenReplies as $reply)
                        @include('posts.partials.comment', ['post' => $post, 'comment' => $reply, 'depth' => ($depth ?? 0) + 1])
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</article>