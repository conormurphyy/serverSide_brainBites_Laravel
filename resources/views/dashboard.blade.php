@extends('layouts.site')

@section('title', 'BrainBites | Dashboard')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Creator Command Center</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">{{ $isAdminView ? 'Admin Dashboard' : 'Contributor Dashboard' }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">
                {{ $isAdminView ? 'Moderate all posts, keep quality high, and support creators.' : 'Track your momentum, manage posts, and keep BrainBites visually explosive.' }}
            </p>
            <div class="mt-5">
                <a href="{{ route('posts.create') }}" class="bb-button">Create New Post</a>
            </div>
        </div>

        <div class="bb-focus-card">
            <h2 class="text-lg font-bold text-white">Publishing Rhythm</h2>
            <p class="mt-2 text-sm text-cyan-100/85">Use these metrics to decide your next post and keep momentum.</p>

            <div class="mt-4 space-y-3 text-sm text-cyan-100">
                <div>
                    <p class="mb-1">Public Share</p>
                    <div class="h-2 rounded-full bg-white/20">
                        <div class="h-2 rounded-full bg-cyan-300" style="width: {{ $stats['total_posts'] > 0 ? round(($stats['public_posts'] / $stats['total_posts']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <p class="mb-1">Avg Likes / Post</p>
                    <div class="h-2 rounded-full bg-white/20">
                        <div class="h-2 rounded-full bg-lime-300" style="width: {{ min(100, $stats['total_posts'] > 0 ? round(($stats['total_likes'] / max(1, $stats['total_posts'])) * 20) : 0) }}%"></div>
                    </div>
                </div>
                <div>
                    <p class="mb-1">Avg Comments / Post</p>
                    <div class="h-2 rounded-full bg-white/20">
                        <div class="h-2 rounded-full bg-fuchsia-300" style="width: {{ min(100, $stats['total_posts'] > 0 ? round(($stats['total_comments'] / max(1, $stats['total_posts'])) * 20) : 0) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-8 grid gap-4 sm:grid-cols-4">
        <article class="bb-model-card" data-tilt-card>
            <div data-tilt-glare class="bb-post-glare"></div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Posts</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_posts'] }}</p>
        </article>

        <article class="bb-model-card" data-tilt-card>
            <div data-tilt-glare class="bb-post-glare"></div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public Posts</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['public_posts'] }}</p>
        </article>

        <article class="bb-model-card" data-tilt-card>
            <div data-tilt-glare class="bb-post-glare"></div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Likes</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_likes'] }}</p>
        </article>

        <article class="bb-model-card" data-tilt-card>
            <div data-tilt-glare class="bb-post-glare"></div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Comments</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total_comments'] }}</p>
        </article>
    </section>

    <section class="mb-8 grid gap-5 lg:grid-cols-2">
        <article class="bb-card" id="dashboardPinboard">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">Key Takeaways Pinboard</h2>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">3 to 5 lines</span>
            </div>
            <div id="takeawaysList" class="mt-4 grid gap-3"></div>
            <p id="takeawaysEmpty" class="mt-2 text-sm text-slate-600">Pin key lines from post paragraphs to build this board.</p>
        </article>

        <article class="bb-card" id="dashboardStreakSummary">
            <h2 class="text-xl font-bold text-slate-900">Learning Momentum</h2>
            <p id="dashboardStreakText" class="mt-3 text-sm font-semibold text-slate-700">Streak: 0 days</p>
            <div class="mt-2 h-2 rounded-full bg-slate-200">
                <div id="dashboardGoalBar" class="h-2 rounded-full bg-lime-500" style="width: 0%"></div>
            </div>
            <p id="dashboardGoalText" class="mt-2 text-xs text-slate-600">0 / 5 posts this week</p>
        </article>
    </section>

    @if ($isAdminView)
        <section class="bb-card mb-8">
            <h2 class="text-xl font-bold text-slate-900">Create Category</h2>
            <p class="mt-1 text-sm text-slate-600">Add a new category that contributors can assign to posts.</p>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_auto] sm:items-end">
                @csrf
                <div>
                    <label for="dashboard_category_name" class="bb-label">Name</label>
                    <input
                        id="dashboard_category_name"
                        name="name"
                        type="text"
                        class="bb-input"
                        value="{{ old('name') }}"
                        required
                        maxlength="100"
                    >
                    @if ($errors->createCategory->has('name'))
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->createCategory->first('name') }}</p>
                    @endif
                </div>

                <div>
                    <label for="dashboard_category_description" class="bb-label">Description (optional)</label>
                    <input
                        id="dashboard_category_description"
                        name="description"
                        type="text"
                        class="bb-input"
                        value="{{ old('description') }}"
                        maxlength="500"
                    >
                    @if ($errors->createCategory->has('description'))
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->createCategory->first('description') }}</p>
                    @endif
                </div>

                <button type="submit" class="bb-button">Create</button>
            </form>
        </section>
    @endif

    <section class="bb-card overflow-x-auto">
        <h2 class="mb-4 text-xl font-bold text-slate-900">{{ $isAdminView ? 'All Posts' : 'Your Posts' }}</h2>

        @if ($posts->isEmpty())
            <p class="text-sm text-slate-600">You have not created any posts yet.</p>
        @else
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">Image</th>
                        <th class="px-3 py-2">Title</th>
                        <th class="px-3 py-2">Category</th>
                        <th class="px-3 py-2">Visibility</th>
                        <th class="px-3 py-2">Moderation</th>
                        <th class="px-3 py-2">Likes</th>
                        <th class="px-3 py-2">Comments</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-3">
                                <img
                                    src="{{ $post->image_source }}"
                                    alt="{{ $post->title }}"
                                    class="h-16 w-24 rounded-lg object-cover"
                                >
                            </td>
                            <td class="px-3 py-3 font-semibold text-slate-800">{{ $post->title }}</td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ $post->category->name }}
                                @if ($isAdminView)
                                    <div class="mt-1 text-xs text-slate-500">by {{ $post->user->name }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-600">{{ $post->is_public ? 'Public' : 'Draft' }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ ucfirst($post->approval_status ?? 'approved') }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $post->likes_count }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $post->comments_count }}</td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('posts.show', $post) }}" class="bb-button-secondary">View</a>
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
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
@endsection
