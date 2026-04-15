<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function __invoke(Request $request, Post $post): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        if ($request->user()->cannot('view', $post)) {
            abort(403);
        }

        $bookmark = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return back()->with('status', 'Post removed from bookmarks.');
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        return back()->with('status', 'Post saved to bookmarks.');
    }

    public function index(Request $request): View
    {
        abort_if($request->user()->isAdmin(), 403);

        $bookmarkedPosts = Post::query()
            ->with(['user', 'category', 'likes', 'bookmarks'])
            ->withCount('likes')
            ->whereHas('bookmarks', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id);
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(9);

        return view('posts.bookmarks', [
            'bookmarkedPosts' => $bookmarkedPosts,
        ]);
    }
}
