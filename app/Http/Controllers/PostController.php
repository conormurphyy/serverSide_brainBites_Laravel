<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $postsQuery = Post::query()
            ->with(['user', 'category', 'likes'])
            ->withCount('likes')
            ->when(auth()->check(), function ($query): void {
                $query->where(function ($nested): void {
                    $nested->where('is_public', true)
                        ->orWhere('user_id', auth()->id());
                });
            }, function ($query): void {
                $query->public();
            });

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
            ->with(['user', 'category', 'likes'])
            ->withCount('likes')
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

        return view('posts.index', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'categoryMapData' => $categoryMapData,
            'search' => $search,
            'selectedCategory' => $category,
            'sort' => $sort,
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
        $data['published_at'] = $data['is_public'] ? now() : null;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        unset($data['image']);

        $post = Post::create($data);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'Post published successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        if (! $post->is_public && (! auth()->check() || auth()->user()->cannot('view', $post))) {
            abort(403);
        }

        $post->load(['user', 'category', 'likes']);
        $relatedPosts = Post::query()
            ->public()
            ->with(['user', 'category'])
            ->withCount('likes')
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
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
            if (! str_starts_with($post->image_path, 'http')) {
                Storage::disk('public')->delete($post->image_path);
            }

            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        unset($data['image']);

        if (($data['is_public'] ?? false) && ! $post->published_at) {
            $data['published_at'] = now();
        }

        if (($data['is_public'] ?? true) === false) {
            $data['published_at'] = null;
        }

        $post->update($data);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        if ($post->image_path && ! str_starts_with($post->image_path, 'http')) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Post deleted successfully.');
    }
}
