@extends('layouts.site')

@section('title', 'BrainBites | Profile')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-chip">Account Studio</p>
            <h1 class="bb-title-font mt-3 text-4xl text-white sm:text-5xl">Your Profile</h1>
            <p class="mt-3 max-w-2xl text-sm text-cyan-100/85 sm:text-base">
                Update your identity, security settings, and connected sign-in methods.
            </p>
        </div>

        <div class="bb-focus-card">
            <h2 class="text-lg font-bold text-white">Quick Tip</h2>
            <p class="mt-2 text-sm text-cyan-100/85">Keep your profile photo and password current for a stronger account.</p>
        </div>
    </section>

    <section class="space-y-6">
        <article class="bb-card">
            @include('profile.partials.update-profile-information-form')
        </article>

        <article class="bb-card">
            @include('profile.partials.update-password-form')
        </article>

        <article class="bb-card">
            @include('profile.partials.delete-user-form')
        </article>
    </section>
@endsection
