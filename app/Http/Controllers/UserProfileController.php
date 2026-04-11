<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(User $user): View
    {
        abort_if(! $user->username, 404);

        $publicPostsQuery = Post::query()
            ->public()
            ->where('user_id', $user->id)
            ->with(['category'])
            ->withCount(['likes', 'comments']);

        $recentPosts = (clone $publicPostsQuery)
            ->latest('published_at')
            ->latest('created_at')
            ->take(6)
            ->get();

        $topPosts = (clone $publicPostsQuery)
            ->orderByDesc('likes_count')
            ->latest('published_at')
            ->take(3)
            ->get();

        $stats = [
            'followers' => $user->followerUsers()->count(),
            'following' => $user->followingUsers()->count(),
            'public_posts' => Post::query()->public()->where('user_id', $user->id)->count(),
            'total_likes' => Post::query()
                ->public()
                ->where('user_id', $user->id)
                ->withCount('likes')
                ->get()
                ->sum('likes_count'),
        ];

        $isFollowing = auth()->check() && ! auth()->user()->isAdmin() && auth()->id() !== $user->id
            ? auth()->user()->followingUsers()->whereKey($user->id)->exists()
            : false;

        return view('users.show', [
            'profileUser' => $user,
            'recentPosts' => $recentPosts,
            'topPosts' => $topPosts,
            'stats' => $stats,
            'isFollowing' => $isFollowing,
        ]);
    }
}
