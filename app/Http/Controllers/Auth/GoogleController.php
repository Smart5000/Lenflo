<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirect(GoogleOAuthService $google): RedirectResponse
    {
        return redirect($google->getRedirectUrl());
    }

    public function callback(GoogleOAuthService $google): RedirectResponse
    {
        $state = request()->query('state');

        if (! $google->validateState($state)) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Invalid authentication state. Please try again.']);
        }

        $code = request()->query('code');

        if (! $code) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google authentication was cancelled.']);
        }

        try {
            $tokenData = $google->exchangeCodeForToken($code);
            $googleUser = $google->getUserInfo($tokenData['access_token']);
            $user = $google->findOrCreateUser($googleUser);

            Auth::login($user, remember: true);

            if ($user->profile && ! $user->profile->onboarding_completed) {
                return redirect()->route('onboarding');
            }

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('login')
                ->withErrors(['google' => 'Authentication failed. Please try again.']);
        }
    }
}
