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

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return $this->redirectToGoogle('login', 'Google sign-in is not configured yet. Add the Google credentials in .env first.');
    }

    public function link(Request $request): RedirectResponse
    {
        $request->session()->put('google_linking', true);

        return $this->redirectToGoogle('profile.edit', 'Google sign-in is not configured yet. Add the Google credentials in .env first.');
    }

    public function callback(Request $request): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret')) || blank(config('services.google.redirect'))) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google sign-in is not configured yet. Add the Google credentials in .env first.',
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google sign-in could not be completed. Please try again.',
                ]);
        }

        $isLinking = (bool) $request->session()->pull('google_linking', false);

        if (! $googleUser->getEmail()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google did not return an email address for this account.',
                ]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->first();

        if ($user) {
            if ($isLinking && auth()->check() && $user->id !== $request->user()->id) {
                return redirect()
                    ->route('profile.edit')
                    ->withErrors([
                        'profile_photo' => 'That Google account is already connected to another user.',
                    ]);
            }

            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } elseif ($isLinking) {
            if (! $request->user()) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Please sign in first before linking a Google account.',
                    ]);
            }

            $request->user()->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $request->user()->email_verified_at ?? now(),
            ])->save();

            return redirect()
                ->route('profile.edit')
                ->with('status', 'Google account linked successfully.');
        } else {
            $existingUser = User::query()
                ->where('email', $googleUser->getEmail())
                ->first();

            if ($existingUser) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'An account already exists for this email. Sign in with your password, then link Google from your profile.',
                    ]);
            }

            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'role' => 'reader',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard', absolute: false))
                ->with('status', 'Signed in with Google.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('status', 'Signed in with Google.');
    }

    private function redirectToGoogle(string $failureRoute, string $failureMessage): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret')) || blank(config('services.google.redirect'))) {
            return redirect()
                ->route($failureRoute)
                ->withErrors([
                    'email' => $failureMessage,
                ]);
        }

        return Socialite::driver('google')->redirect();
    }
}
