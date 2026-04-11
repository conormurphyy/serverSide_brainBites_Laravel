<section>
    <header>
        <h2 class="text-xl font-bold text-slate-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <div class="mt-6 flex items-center gap-4">
        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-cyan-200">
        <div>
            <p class="text-sm font-medium text-slate-900">Profile picture</p>
            <p class="text-sm text-slate-600">Upload a square JPG, PNG, WebP, or AVIF image.</p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-900">Google account</p>
                <p class="text-sm text-slate-600">
                    {{ $user->google_id ? 'Connected to Google sign-in.' : 'Not connected yet. Link Google for one-click sign-in.' }}
                </p>
            </div>

            @if ($user->google_id)
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Connected</span>
            @else
                <a href="{{ route('auth.google.link') }}" class="bb-button-secondary inline-flex justify-center">
                    Connect Google Account
                </a>
            @endif
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="bb-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="bb-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="username" class="bb-label">Public Username</label>
            <input id="username" name="username" type="text" class="bb-input" value="{{ old('username', $user->username) }}" required maxlength="60" autocomplete="username" placeholder="your_username">
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
            <p class="mt-2 text-sm text-slate-600">Used for your public profile URL: /u/{{ old('username', $user->username) }}</p>
        </div>

        <div>
            <label for="email" class="bb-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="bb-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-700">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-cyan-700 hover:text-cyan-800 rounded-md focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="profile_photo" class="bb-label">{{ __('Profile Picture') }}</label>
            <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp,image/avif" class="bb-input">
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            <p class="mt-2 text-sm text-slate-600">Leave this empty to keep your current picture.</p>
        </div>

        <div>
            <label for="bio" class="bb-label">Bio</label>
            <textarea id="bio" name="bio" rows="4" class="bb-textarea" maxlength="600" placeholder="Tell people what you love learning and sharing...">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bb-button">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
