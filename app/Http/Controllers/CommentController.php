<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse|JsonResponse
    {
        $this->assertCanViewPost($request, $post);

        if (trim((string) $request->input('body', '')) === '') {
            $request->merge(['body' => null]);
        }

        $data = $request->validate([
            'body' => ['nullable', 'string', 'min:2', 'max:1000'],
            'parent_comment_id' => ['nullable', 'integer', 'exists:comments,id'],
            'image' => ['nullable', 'image', 'max:5120'],
            'voice_note' => ['nullable', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/wav,audio/x-wav,audio/aac', 'max:2048'],
            'voice_note_duration' => ['nullable', 'numeric', 'min:0.1', 'max:30'],
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        $hasImage = $request->hasFile('image');
        $hasVoiceNote = $request->hasFile('voice_note');

        if ($body === '' && ! $hasImage && ! $hasVoiceNote) {
            throw ValidationException::withMessages([
                'body' => 'Add text, an image, or a voice note before posting.',
            ]);
        }

        $parentComment = null;

        if (! empty($data['parent_comment_id'])) {
            $parentComment = Comment::query()
                ->whereKey($data['parent_comment_id'])
                ->where('post_id', $post->id)
                ->firstOrFail();
        }

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
            'body' => $body,
            'parent_comment_id' => $parentComment?->id,
            'image_path' => $hasImage ? $request->file('image')->store('comments/images', 'public') : null,
            'voice_note_path' => $hasVoiceNote ? $request->file('voice_note')->store('comments/voice-notes', 'public') : null,
            'voice_note_duration' => $hasVoiceNote ? (isset($data['voice_note_duration']) ? (float) $data['voice_note_duration'] : null) : null,
        ]);

        if ($request->expectsJson()) {
            $comment->load('user');

            return response()->json([
                'message' => 'Comment posted successfully.',
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'image_url' => $comment->image_url,
                    'voice_note_url' => $comment->voice_note_url,
                    'voice_note_duration' => $comment->voice_note_duration,
                    'parent_comment_id' => $comment->parent_comment_id,
                    'created_at_human' => $comment->created_at?->diffForHumans() ?? 'just now',
                    'user' => [
                        'name' => $comment->user->name,
                        'profile_photo_url' => $comment->user->profile_photo_url,
                    ],
                ],
            ]);
        }

        return back()->with('status', 'Comment posted successfully.');
    }

    public function destroy(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        $this->assertCanViewPost($request, $post);

        abort_unless($comment->post_id === $post->id, 404);
        abort_unless($request->user()->isAdmin() || $request->user()->id === $comment->user_id, 403);

        $this->deleteCommentTree($comment);

        return back()->with('status', 'Comment removed.');
    }

    private function assertCanViewPost(Request $request, Post $post): void
    {
        $isScheduledForFuture = $post->is_public
            && $post->published_at
            && $post->published_at->isFuture();

        if ((! $post->is_public || $isScheduledForFuture) && (! auth()->check() || auth()->user()->cannot('view', $post))) {
            abort(403);
        }
    }

    private function deleteCommentTree(Comment $comment): void
    {
        Comment::query()
            ->where('parent_comment_id', $comment->id)
            ->get()
            ->each(function (Comment $reply): void {
                $this->deleteCommentTree($reply);
            });

        if ($comment->image_path) {
            Storage::disk('public')->delete($comment->image_path);
        }

        if ($comment->voice_note_path) {
            Storage::disk('public')->delete($comment->voice_note_path);
        }

        $comment->delete();
    }
}