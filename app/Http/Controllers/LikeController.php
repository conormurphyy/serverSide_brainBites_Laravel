<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __invoke(Request $request, Post $post): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        if ($request->user()->cannot('view', $post)) {
            abort(403);
        }

        $like = Like::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();

            return back()->with('status', 'Post removed from likes.');
        }

        Like::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        return back()->with('status', 'Post added to likes.');
    }
}
