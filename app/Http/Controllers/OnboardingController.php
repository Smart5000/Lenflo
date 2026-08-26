<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->profile && $user->profile->onboarding_completed) {
            return redirect()->route('feed');
        }

        return Inertia::render('onboarding/index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
            ],
            'countries' => fn () => DB::table('countries')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['code', 'name']),
            'languages' => fn () => DB::table('languages')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['code', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'birth_date' => ['required', 'date', 'before:-3 years', 'after:1900-01-01'],
            'country_code' => ['nullable', 'string', 'size:2', 'exists:countries,code'],
            'language_code' => ['nullable', 'string', 'max:5', 'exists:languages,code'],
        ]);

        $user = $request->user();

        $user->forceFill(['birth_date' => $validated['birth_date']])->save();

        $profile = $user->profile;

        if (! $profile) {
            $profile = $user->profile()->create([]);
        }

        $profile->update([
            'country_code' => $validated['country_code'] ?? null,
            'language_code' => $validated['language_code'] ?? null,
            'onboarding_completed' => true,
        ]);

        return redirect()->route('feed');
    }
}
