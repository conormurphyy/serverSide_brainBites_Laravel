@extends('layouts.site')

@section('title', 'About BrainBites')

@section('content')
    <section class="bb-hero-grid mb-8">
        <div class="bb-hero-content">
            <p class="bb-kicker">Our Mission</p>
            <h1 class="bb-title-font text-4xl leading-tight text-white sm:text-5xl">
                Build a place where curiosity feels electric.
            </h1>
            <p class="mt-5 max-w-xl text-sm text-cyan-50/90 sm:text-base">
                BrainBites is a visual-first Q&A experience designed to make learning feel alive,
                collaborative, and beautifully interactive.
            </p>
        </div>

        <div class="bb-focus-card text-cyan-50">
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Signal Field</p>
            <canvas class="bb-hero-wave-canvas" data-hero-wave="about" aria-hidden="true"></canvas>
            <ul class="mt-3 space-y-2 text-sm text-cyan-100/90">
                <li>Curiosity should be celebrated, not judged.</li>
                <li>Good explanations are short, clear, and visual.</li>
                <li>A community learns faster than any individual.</li>
            </ul>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-3">
        <article class="bb-card">
            <h2 class="text-lg font-bold text-slate-900">Question Capsules</h2>
            <p class="mt-2 text-sm text-slate-600">
                Every post is concise and skimmable, so learning sessions feel focused instead of overwhelming.
            </p>
        </article>

        <article class="bb-card">
            <h2 class="text-lg font-bold text-slate-900">Topic Universe</h2>
            <p class="mt-2 text-sm text-slate-600">
                Explore category relationships through an interactive map powered by real community activity.
            </p>
        </article>

        <article class="bb-card">
            <h2 class="text-lg font-bold text-slate-900">brainBot Companion</h2>
            <p class="mt-2 text-sm text-slate-600">
                Jump into dedicated AI-assisted learning sessions with follow-up questions and rapid summaries.
            </p>
        </article>
    </section>
@endsection
