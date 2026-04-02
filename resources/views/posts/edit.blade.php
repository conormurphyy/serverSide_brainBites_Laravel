@extends('layouts.site')

@section('title', 'BrainBites | Edit Post')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Refine Mode</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Edit Post</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">Polish your explanation, refresh visuals, and make every section easier to absorb.</p>
        </div>

        <div class="bb-focus-card">
            <h2 class="text-lg font-bold text-white">Revision Checklist</h2>
            <ul class="mt-3 space-y-2 text-sm text-cyan-100/85">
                <li>Keep the first sentence instantly clear.</li>
                <li>Remove jargon where possible.</li>
                <li>Update image if the visual story can improve.</li>
            </ul>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="bb-card">
            <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                @include('posts._form', ['post' => $post])
            </form>
        </div>

        <aside class="space-y-4">
            <div class="bb-model-card" data-tilt-card>
                <div data-tilt-glare class="bb-post-glare"></div>
                <img
                    src="{{ str_starts_with($post->image_path, 'http') ? $post->image_path : Storage::url($post->image_path) }}"
                    alt="{{ $post->title }}"
                    class="h-44 w-full rounded-xl object-cover"
                >
                <h3 class="mt-4 text-lg font-bold text-slate-900">Current Visual</h3>
                <p class="mt-2 text-sm text-slate-600">Use this as your baseline and evolve the story design.</p>
            </div>

            <div class="bb-model-card">
                <h3 class="text-lg font-bold text-slate-900">Live Card Preview</h3>
                <p class="mt-2 text-sm text-slate-600">Preview updates while you edit this post.</p>
                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-3">
                    <img id="previewImage" src="{{ str_starts_with($post->image_path, 'http') ? $post->image_path : Storage::url($post->image_path) }}" alt="Preview" class="h-36 w-full rounded-lg object-cover">
                    <p id="previewCategory" class="mt-3 text-xs font-semibold uppercase tracking-wide text-cyan-700">{{ $post->category->name }}</p>
                    <p id="previewTitle" class="mt-1 text-sm font-semibold text-slate-900">{{ $post->title }}</p>
                    <p id="previewSummary" class="mt-1 text-xs text-slate-600">{{ $post->summary }}</p>
                </div>
            </div>
        </aside>
    </section>
@endsection
