<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $category = trim((string) $request->string('category'));
        $sort = trim((string) $request->string('sort', 'newest'));
        $feed = trim((string) $request->string('feed', 'all'));

        $followingIds = auth()->check()
            ? auth()->user()->followingUsers()->pluck('users.id')
            : collect();

        $postsQuery = Post::query()
            ->with(['user', 'category', 'likes', 'bookmarks'])
            ->withCount(['likes', 'comments'])
            ->when(auth()->check(), function ($query): void {
                $query->where(function ($nested): void {
                    $nested->where(function ($publicNested): void {
                        $publicNested->where('is_public', true)
                            ->where('approval_status', 'approved')
                            ->where(function ($visibilityNested): void {
                                $visibilityNested->whereNull('published_at')
                                    ->orWhere('published_at', '<=', now());
                            });
                    })
                        ->orWhere('user_id', auth()->id());
                });
            }, function ($query): void {
                $query->public();
            });

        if ($feed === 'following' && auth()->check()) {
            $postsQuery->whereIn('user_id', $followingIds->all());
        }

        if ($search !== '') {
            $postsQuery->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('summary', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%')
                    ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                        $categoryQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($category !== '') {
            $postsQuery->whereHas('category', function ($query) use ($category): void {
                $query->where('slug', $category);
            });
        }

        if ($sort === 'popular') {
            $postsQuery->orderByDesc('likes_count')->orderByDesc('published_at');
        } elseif ($sort === 'oldest') {
            $postsQuery->orderBy('published_at')->orderBy('created_at');
        } else {
            $postsQuery->orderByDesc('published_at')->orderByDesc('created_at');
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        $featuredPosts = Post::query()
            ->public()
            ->with(['user', 'category', 'likes', 'bookmarks'])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('likes_count')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        $categories = Category::query()->orderBy('name')->get();
        $categoryMapData = $categories
            ->map(function (Category $category): array {
                $publicCount = $category->posts()->public()->count();
                $latestTitle = $category->posts()
                    ->public()
                    ->latest('published_at')
                    ->value('title');

                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $publicCount,
                    'latestTitle' => $latestTitle,
                ];
            })
            ->filter(fn (array $item): bool => $item['count'] > 0)
            ->values();

        $communityStats = [
            'public_posts' => Post::query()->public()->count(),
            'public_likes' => Like::query()->whereHas('post', fn ($query) => $query->public())->count(),
            'active_categories' => $categoryMapData->count(),
            'contributors' => User::query()->whereHas('posts', fn ($query) => $query->public())->count(),
        ];

        $topContributors = User::query()
            ->withCount([
                'posts as public_posts_count' => fn ($query) => $query->public(),
                'likes',
                'followerUsers as followers_count',
            ])
            ->orderByDesc('public_posts_count')
            ->orderByDesc('likes_count')
            ->take(5)
            ->get();

        $freshPicks = Post::query()
            ->public()
            ->with(['user', 'category', 'bookmarks'])
            ->withCount(['likes', 'comments'])
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('posts.index', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'categoryMapData' => $categoryMapData,
            'communityStats' => $communityStats,
            'topContributors' => $topContributors,
            'freshPicks' => $freshPicks,
            'search' => $search,
            'selectedCategory' => $category,
            'sort' => $sort,
            'feed' => $feed,
            'followingIds' => $followingIds,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('posts.create', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = Post::uniqueSlug($data['title']);
        if (! ($data['is_public'] ?? false)) {
            $data['published_at'] = null;
            $data['approval_status'] = 'draft';
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['rejected_at'] = null;
        } elseif (! empty($data['published_at'])) {
            $data['published_at'] = $data['published_at'];
        } else {
            $data['published_at'] = now();
        }

        if (($data['is_public'] ?? false) === true) {
            if ($request->user()->isAdmin()) {
                $data['approval_status'] = 'approved';
                $data['approved_by'] = $request->user()->id;
                $data['approved_at'] = now();
                $data['rejected_at'] = null;
            } else {
                $data['approval_status'] = 'pending';
                $data['approved_by'] = null;
                $data['approved_at'] = null;
                $data['rejected_at'] = null;
            }
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $data['image_mime'] = $image->getMimeType() ?: 'image/jpeg';
            $data['image_base64'] = base64_encode((string) file_get_contents($image->getRealPath()));
            $data['image_path'] = 'embedded';
        }

        unset($data['image']);

        $post = Post::create($data);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', $post->approval_status === 'pending'
                ? 'Post submitted for admin approval. It will go live after review.'
                : 'Post published successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): View
    {
        if (! auth()->check() && ! $post->isPublishedPublicly()) {
            abort(403);
        }

        if (auth()->check() && auth()->user()->cannot('view', $post)) {
            abort(403);
        }

        $commentsSort = trim((string) $request->string('comments_sort', 'top'));
        if (! in_array($commentsSort, ['top', 'new'], true)) {
            $commentsSort = 'top';
        }

        $authUserId = auth()->id();

        $post->load([
            'user',
            'category',
            'likes',
            'bookmarks',
            'comments' => function ($query) use ($authUserId): void {
                $query->with('user')
                    ->withCount('votes')
                    ->when($authUserId, function ($nested) use ($authUserId): void {
                        $nested->withExists([
                            'votes as is_upvoted_by_auth' => fn ($voteQuery) => $voteQuery->where('user_id', $authUserId),
                        ]);
                    })
                    ->with([
                        'replies' => function ($replyQuery) use ($authUserId): void {
                            $replyQuery->with('user')
                                ->withCount('votes')
                                ->when($authUserId, function ($nestedReplies) use ($authUserId): void {
                                    $nestedReplies->withExists([
                                        'votes as is_upvoted_by_auth' => fn ($voteQuery) => $voteQuery->where('user_id', $authUserId),
                                    ]);
                                })
                                ->with([
                                    'replies' => function ($deepReplyQuery) use ($authUserId): void {
                                        $deepReplyQuery->with('user')
                                            ->withCount('votes')
                                            ->when($authUserId, function ($deepNested) use ($authUserId): void {
                                                $deepNested->withExists([
                                                    'votes as is_upvoted_by_auth' => fn ($voteQuery) => $voteQuery->where('user_id', $authUserId),
                                                ]);
                                            });
                                    },
                                ]);
                        },
                    ]);
            },
        ]);

        $rootComments = $post->comments
            ->whereNull('parent_comment_id')
            ->sort(function ($a, $b) use ($commentsSort): int {
                if ($commentsSort === 'new') {
                    return $b->created_at <=> $a->created_at;
                }

                if ($a->votes_count === $b->votes_count) {
                    return $b->created_at <=> $a->created_at;
                }

                return $b->votes_count <=> $a->votes_count;
            })
            ->values();

        $isFollowingAuthor = auth()->check()
            ? auth()->user()->followingUsers()->whereKey($post->user_id)->exists()
            : false;

        $relatedPosts = Post::query()
            ->public()
            ->with(['user', 'category', 'bookmarks'])
            ->withCount(['likes', 'comments'])
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'isFollowingAuthor' => $isFollowingAuthor,
            'rootComments' => $rootComments,
            'commentsSort' => $commentsSort,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $data = $request->validated();
        $data['slug'] = Post::uniqueSlug($data['title'], $post->id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $data['image_mime'] = $image->getMimeType() ?: 'image/jpeg';
            $data['image_base64'] = base64_encode((string) file_get_contents($image->getRealPath()));
            $data['image_path'] = 'embedded';
        }

        unset($data['image']);

        if (($data['is_public'] ?? true) === false) {
            $data['published_at'] = null;
            $data['approval_status'] = 'draft';
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['rejected_at'] = null;
        } elseif (! empty($data['published_at'])) {
            $data['published_at'] = $data['published_at'];
        } elseif (! $post->published_at) {
            $data['published_at'] = now();
        } else {
            unset($data['published_at']);
        }

        if (($data['is_public'] ?? true) === true) {
            if ($request->user()->isAdmin()) {
                $data['approval_status'] = 'approved';
                $data['approved_by'] = $request->user()->id;
                $data['approved_at'] = now();
                $data['rejected_at'] = null;
            } else {
                $data['approval_status'] = 'pending';
                $data['approved_by'] = null;
                $data['approved_at'] = null;
                $data['rejected_at'] = null;
            }
        }

        $post->update($data);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', $post->approval_status === 'pending'
                ? 'Post updates submitted for admin approval.'
                : 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Post deleted successfully.');
    }
}
