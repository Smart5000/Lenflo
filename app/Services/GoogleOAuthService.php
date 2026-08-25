<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleOAuthService
{
    public function getRedirectUrl(): string
    {
        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'select_account',
            'state' => Str::random(40),
        ];

        session(['google_oauth_state' => $params['state']]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params);
    }

    public function validateState(string $state): bool
    {
        $stored = session('google_oauth_state');
        session()->forget('google_oauth_state');

        return $stored && hash_equals($stored, $state);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Google token exchange failed: '.$response->body());
        }

        return $response->json();
    }

    public function getUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if ($response->failed()) {
            throw new \RuntimeException('Google userinfo request failed: '.$response->body());
        }

        return $response->json();
    }

    public function findOrCreateUser(array $googleUser): User
    {
        $email = $googleUser['email'] ?? null;

        if (! $email) {
            throw new \RuntimeException('Google account has no email.');
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        $user = User::create([
            'name' => $googleUser['name'] ?? Str::before($email, '@'),
            'email' => $email,
            'password' => null,
            'email_verified_at' => now(),
        ]);

        $user->profile()->create([
            'country_code' => null,
            'language_code' => null,
            'onboarding_completed' => false,
        ]);

        return $user;
    }
}
