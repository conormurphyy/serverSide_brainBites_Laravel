<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentVoteController extends Controller
{
    public function __invoke(Request $request, Post $post, Comment $comment): RedirectResponse|JsonResponse
    {
        abort_if($request->user()->isAdmin(), 403);
        abort_unless($comment->post_id === $post->id, 404);
        abort_if($request->user()->cannot('view', $post), 403);

        $vote = CommentVote::query()
            ->where('user_id', $request->user()->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($vote) {
            $vote->delete();

            $upvotes = $comment->votes()->count();
            $message = 'Removed your upvote from the comment.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'upvotes' => $upvotes,
                    'upvoted' => false,
                ]);
            }

            return back()->with('status', $message);
        }

        CommentVote::create([
            'user_id' => $request->user()->id,
            'comment_id' => $comment->id,
        ]);

        $upvotes = $comment->votes()->count();
        $message = 'Marked comment as helpful.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'upvotes' => $upvotes,
                'upvoted' => true,
            ]);
        }

        return back()->with('status', $message);
    }
}
