@extends('layouts.site')

@section('title', 'Contact BrainBites')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-kicker">Contact</p>
            <h1 class="bb-title-font text-4xl text-white sm:text-5xl">Let’s Build Something Better</h1>
            <p class="mt-4 max-w-xl text-sm text-cyan-100/90 sm:text-base">
                Have feedback, feature ideas, or partnership requests? Send a note and we will get back to you.
            </p>
        </div>

        <div class="bb-focus-card text-cyan-50">
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Pulse Link</p>
            <canvas class="bb-hero-wave-canvas" data-hero-wave="contact" aria-hidden="true"></canvas>
            <h3 class="mt-1 text-lg font-semibold">Use This Form</h3>
            <p class="mt-2 text-sm text-cyan-100/90">
                Messages are logged directly into the app so the team can respond quickly.
            </p>
        </div>
    </section>

    <section class="bb-glass p-5 sm:p-8">
        <form action="{{ route('contact.submit') }}" method="POST" class="grid gap-5 md:grid-cols-2">
            @csrf

            <div>
                <label for="name" class="bb-label">Name</label>
                <input id="name" name="name" type="text" class="bb-input" value="{{ old('name') }}" required>
                @error('name')<p class="bb-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="bb-label">Email</label>
                <input id="email" name="email" type="email" class="bb-input" value="{{ old('email') }}" required>
                @error('email')<p class="bb-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="topic" class="bb-label">Topic</label>
                <input id="topic" name="topic" type="text" class="bb-input" value="{{ old('topic') }}" placeholder="Feedback, bug report, partnership..." required>
                @error('topic')<p class="bb-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="message" class="bb-label">Message</label>
                <textarea id="message" name="message" rows="7" class="bb-textarea" required>{{ old('message') }}</textarea>
                @error('message')<p class="bb-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2 flex gap-3">
                <button type="submit" class="bb-button">Send Message</button>
                <a href="{{ route('home') }}" class="bb-button-secondary">Back Home</a>
            </div>
        </form>
    </section>
@endsection
