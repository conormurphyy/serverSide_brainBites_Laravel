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

    <div class="mt-4 overflow-hidden rounded-2xl border border-cyan-200/50 shadow-sm">
        <img src="{{ $user->cover_image_url }}" alt="{{ $user->name }} cover image" class="h-32 w-full object-cover sm:h-40">
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

    @php
        $socialLabels = [
            'website' => 'Website',
            'x' => 'X',
            'github' => 'GitHub',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
        ];
        $socialLinks = old('social_links', $user->social_links ?? []);
        $topicBadgesValue = old('topic_badges', collect($user->topic_badges ?? [])->implode(', '));
        $selectedPinnedPosts = collect(old('pinned_posts', $pinnedPostIds ?? []))
            ->map(fn ($id) => (int) $id)
            ->all();
    @endphp

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6"
        enctype="multipart/form-data"
        x-data="{
            previewName: @js(old('name', $user->name)),
            previewUsername: @js(old('username', $user->username)),
            previewBio: @js(old('bio', $user->bio)),
            topicBadges: @js($topicBadgesValue),
            social: {
                website: @js($socialLinks['website'] ?? ''),
                x: @js($socialLinks['x'] ?? ''),
                github: @js($socialLinks['github'] ?? ''),
                linkedin: @js($socialLinks['linkedin'] ?? ''),
                youtube: @js($socialLinks['youtube'] ?? ''),
            },
            avatarUrl: @js($user->profile_photo_url),
            coverUrl: @js($user->cover_image_url),
            loadPreview(event, target) {
                const file = event.target.files && event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    if (target === 'avatar') {
                        this.avatarUrl = e.target?.result || this.avatarUrl;
                        return;
                    }

                    this.coverUrl = e.target?.result || this.coverUrl;
                };
                reader.readAsDataURL(file);
            },
            formattedBadges() {
                return this.topicBadges
                    .split(',')
                    .map((item) => item.trim())
                    .filter(Boolean)
                    .slice(0, 8)
                    .map((badge) => badge
                        .split(/\s+/)
                        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ')
                    );
            }
        }"
    >
        @csrf
        @method('patch')

        <div class="rounded-2xl border border-cyan-200/60 bg-cyan-50/40 p-4 sm:p-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-cyan-800">Public Profile Preview</h3>
                <span class="text-xs text-cyan-700">Live as you type</span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <img :src="coverUrl" alt="Profile cover preview" class="h-28 w-full object-cover sm:h-36">
                <div class="-mt-8 px-4 pb-4 sm:px-6 sm:pb-6">
                    <img :src="avatarUrl" alt="Profile photo preview" class="h-16 w-16 rounded-full border-4 border-white object-cover shadow-lg sm:h-20 sm:w-20">
                    <p class="mt-3 text-xl font-bold text-slate-900" x-text="previewName || 'Your Name'"></p>
                    <p class="mt-1 text-sm font-semibold text-cyan-700" x-text="'@' + (previewUsername || 'username')"></p>
                    <p class="mt-2 text-sm text-slate-600" x-text="previewBio || 'Your bio will appear here on your public profile.'"></p>

                    <div class="mt-3" x-show="formattedBadges().length">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Interests</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="badge in formattedBadges()" :key="badge">
                                <span class="inline-flex items-center rounded-full border border-cyan-200/70 bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-900" x-text="badge"></span>
                            </template>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($socialLabels as $platform => $label)
                            <a
                                x-show="social.{{ $platform }}"
                                :href="social.{{ $platform }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700"
                            >{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label for="name" class="bb-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="bb-input" value="{{ old('name', $user->name) }}" x-model="previewName" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="username" class="bb-label">Public Username</label>
            <input id="username" name="username" type="text" class="bb-input" value="{{ old('username', $user->username) }}" x-model="previewUsername" required maxlength="60" autocomplete="username" placeholder="your_username">
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
            <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp,image/avif" class="bb-file-input" @change="loadPreview($event, 'avatar')">
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            <p class="mt-2 text-sm text-slate-600">Leave this empty to keep your current picture.</p>
        </div>

        <div>
            <label for="cover_image" class="bb-label">Cover Image</label>
            <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp,image/avif" class="bb-file-input" @change="loadPreview($event, 'cover')">
            <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
            <p class="mt-2 text-sm text-slate-600">Wide image recommended. Leave empty to keep current cover.</p>
        </div>

        <div>
            <label for="bio" class="bb-label">Bio</label>
            <textarea id="bio" name="bio" rows="4" class="bb-textarea" maxlength="600" x-model="previewBio" placeholder="Tell people what you love learning and sharing...">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="social_website" class="bb-label">Website</label>
                <input id="social_website" name="social_links[website]" type="url" class="bb-input" value="{{ $socialLinks['website'] ?? '' }}" x-model="social.website" placeholder="https://your-site.com">
            </div>
            <div>
                <label for="social_x" class="bb-label">X / Twitter</label>
                <input id="social_x" name="social_links[x]" type="url" class="bb-input" value="{{ $socialLinks['x'] ?? '' }}" x-model="social.x" placeholder="https://x.com/username">
            </div>
            <div>
                <label for="social_github" class="bb-label">GitHub</label>
                <input id="social_github" name="social_links[github]" type="url" class="bb-input" value="{{ $socialLinks['github'] ?? '' }}" x-model="social.github" placeholder="https://github.com/username">
            </div>
            <div>
                <label for="social_linkedin" class="bb-label">LinkedIn</label>
                <input id="social_linkedin" name="social_links[linkedin]" type="url" class="bb-input" value="{{ $socialLinks['linkedin'] ?? '' }}" x-model="social.linkedin" placeholder="https://linkedin.com/in/username">
            </div>
            <div class="sm:col-span-2">
                <label for="social_youtube" class="bb-label">YouTube</label>
                <input id="social_youtube" name="social_links[youtube]" type="url" class="bb-input" value="{{ $socialLinks['youtube'] ?? '' }}" x-model="social.youtube" placeholder="https://youtube.com/@channel">
            </div>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('social_links')" />
        <x-input-error class="mt-2" :messages="$errors->get('social_links.website')" />
        <x-input-error class="mt-2" :messages="$errors->get('social_links.x')" />
        <x-input-error class="mt-2" :messages="$errors->get('social_links.github')" />
        <x-input-error class="mt-2" :messages="$errors->get('social_links.linkedin')" />
        <x-input-error class="mt-2" :messages="$errors->get('social_links.youtube')" />

        <div>
            <label for="topic_badges" class="bb-label">Topic Badges</label>
            <input id="topic_badges" name="topic_badges" type="text" class="bb-input" value="{{ $topicBadgesValue }}" x-model="topicBadges" placeholder="AI Ethics, Web Security, Cognitive Science">
            <x-input-error class="mt-2" :messages="$errors->get('topic_badges')" />
            <p class="mt-2 text-sm text-slate-600">Separate each topic with a comma. Up to 8 badges.</p>
        </div>

        <div>
            <label class="bb-label">Pin Up To 3 Favorite Posts</label>
            @if (($pinnablePosts ?? collect())->isEmpty())
                <p class="text-sm text-slate-600">Publish a few posts first, then pin your favorites.</p>
            @else
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($pinnablePosts as $post)
                        <label class="flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                name="pinned_posts[]"
                                value="{{ $post->id }}"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                @checked(in_array($post->id, $selectedPinnedPosts, true))
                            >
                            <span>{{ $post->title }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('pinned_posts')" />
            <x-input-error class="mt-2" :messages="$errors->get('pinned_posts.*')" />
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
