@extends('layouts.site')

@section('title', 'BrainBites | Create Post')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Create Mode</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Create a New BrainBite</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">Turn complex ideas into visual-first explanations that people actually enjoy reading.</p>
        </div>

        <div class="bb-focus-card">
            <h2 class="text-lg font-bold text-white">What Works Best</h2>
            <ul class="mt-3 space-y-2 text-sm text-cyan-100/85">
                <li>Lead with a specific curiosity question.</li>
                <li>Use summary for the 5-second skim reader.</li>
                <li>Pair each post with a visually clear image.</li>
            </ul>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="bb-card">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" novalidate data-draft-form data-draft-key="bb-draft-post-create">
                @csrf
                @include('posts._form')
            </form>
        </div>

        <aside class="space-y-4">
            <div class="bb-model-card" data-tilt-card>
                <div data-tilt-glare class="bb-post-glare"></div>
                <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1000&q=80" alt="Creative science illustration" class="h-44 w-full rounded-xl object-cover">
                <h3 class="mt-4 text-lg font-bold text-slate-900">Write For Wonder</h3>
                <p class="mt-2 text-sm text-slate-600">Lead with a sharp question, then explain with clear visuals and simple language.</p>
            </div>

            <div class="bb-model-card">
                <h3 class="text-lg font-bold text-slate-900">Live Card Preview</h3>
                <p class="mt-2 text-sm text-slate-600">Your post preview updates as you type.</p>
                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-3">
                    <img id="previewImage" src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=900&q=80" alt="Preview" class="h-36 w-full rounded-lg object-cover">
                    <p id="previewCategory" class="mt-3 text-xs font-semibold uppercase tracking-wide text-cyan-700">Category</p>
                    <p id="previewTitle" class="mt-1 text-sm font-semibold text-slate-900">Your title appears here</p>
                    <p id="previewSummary" class="mt-1 text-xs text-slate-600">Your summary appears here.</p>
                </div>
            </div>
        </aside>
    </section>
@endsection
