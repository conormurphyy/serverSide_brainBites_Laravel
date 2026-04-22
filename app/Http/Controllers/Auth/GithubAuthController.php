<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GithubAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return $this->redirectToGithub('login', 'GitHub sign-in is not configured yet. Add the GitHub credentials in .env first.');
    }

    public function link(Request $request): RedirectResponse
    {
        $request->session()->put('github_linking', true);

        return $this->redirectToGithub('profile.edit', 'GitHub sign-in is not configured yet. Add the GitHub credentials in .env first.');
    }

    public function callback(Request $request): RedirectResponse
    {
        if (blank(config('services.github.client_id')) || blank(config('services.github.client_secret')) || blank(config('services.github.redirect'))) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'GitHub sign-in is not configured yet. Add the GitHub credentials in .env first.',
                ]);
        }

        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (Throwable $exception) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'GitHub sign-in could not be completed. Please try again.',
                ]);
        }

        $isLinking = (bool) $request->session()->pull('github_linking', false);

        if (! $githubUser->getEmail()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'GitHub did not return an email address for this account.',
                ]);
        }

        $user = User::query()
            ->where('github_id', $githubUser->getId())
            ->first();

        if ($user) {
            if ($isLinking && auth()->check() && $user->id !== $request->user()->id) {
                return redirect()
                    ->route('profile.edit')
                    ->withErrors([
                        'profile_photo' => 'That GitHub account is already connected to another user.',
                    ]);
            }

            $user->forceFill([
                'github_id' => $githubUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } elseif ($isLinking) {
            if (! $request->user()) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Please sign in first before linking a GitHub account.',
                    ]);
            }

            $request->user()->forceFill([
                'github_id' => $githubUser->getId(),
                'email_verified_at' => $request->user()->email_verified_at ?? now(),
            ])->save();

            return redirect()
                ->route('profile.edit')
                ->with('status', 'GitHub account linked successfully.');
        } else {
            $existingUser = User::query()
                ->where('email', $githubUser->getEmail())
                ->first();

            if ($existingUser) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'An account already exists for this email. Sign in with your password, then link GitHub from your profile.',
                    ]);
            }

            $user = User::create([
                'name' => $githubUser->getName() ?: $githubUser->getNickname() ?: 'GitHub User',
                'email' => $githubUser->getEmail(),
                'github_id' => $githubUser->getId(),
                'role' => 'reader',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard', absolute: false))
                ->with('status', 'Signed in with GitHub.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('status', 'Signed in with GitHub.');
    }

    private function redirectToGithub(string $failureRoute, string $failureMessage): RedirectResponse
    {
        if (blank(config('services.github.client_id')) || blank(config('services.github.client_secret')) || blank(config('services.github.redirect'))) {
            return redirect()
                ->route($failureRoute)
                ->withErrors([
                    'email' => $failureMessage,
                ]);
        }

        return Socialite::driver('github')->redirect();
    }
}
