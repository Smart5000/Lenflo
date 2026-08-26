<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AgeBand;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content\ContentItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function review(ContentItem $contentItem): Response
    {
        $contentItem->load(['categories', 'reviewer']);

        return Inertia::render('admin/content/review', [
            'contentItem' => $contentItem,
            'ageBandValues' => $contentItem->ageBandValues()->all(),
            'availableCategories' => Category::where('is_active', true)->orderBy('name')->get(),
            'allAgeBands' => collect(AgeBand::cases())->map(fn (AgeBand $band) => [
                'value' => $band->value,
                'label' => $band->label(),
            ])->all(),
        ]);
    }

    public function approve(Request $request, ContentItem $contentItem): RedirectResponse
    {
        Gate::authorize('approve', $contentItem);

        $contentItem->approve($request->user());

        return back()->with('toast', ['type' => 'success', 'message' => "Approved \"{$contentItem->title}\"."]);
    }

    public function reject(Request $request, ContentItem $contentItem): RedirectResponse
    {
        Gate::authorize('reject', $contentItem);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $contentItem->reject($request->user(), $validated['rejection_reason']);

        return back()->with('toast', ['type' => 'success', 'message' => "Rejected \"{$contentItem->title}\"."]);
    }

    public function publish(ContentItem $contentItem): RedirectResponse
    {
        Gate::authorize('publish', $contentItem);

        $contentItem->publish();

        return back()->with('toast', ['type' => 'success', 'message' => "Published \"{$contentItem->title}\"."]);
    }

    public function disable(ContentItem $contentItem): RedirectResponse
    {
        Gate::authorize('disable', $contentItem);

        $contentItem->disable();

        return back()->with('toast', ['type' => 'success', 'message' => "Disabled \"{$contentItem->title}\"."]);
    }

    public function reReview(ContentItem $contentItem): RedirectResponse
    {
        Gate::authorize('update', $contentItem);

        $contentItem->reReview();

        return back()->with('toast', ['type' => 'success', 'message' => "Reset \"{$contentItem->title}\" for re-review."]);
    }

    public function updateClassification(Request $request, ContentItem $contentItem): RedirectResponse
    {
        Gate::authorize('update', $contentItem);

        $validated = $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'age_bands' => ['required', 'array', 'min:1'],
            'age_bands.*' => ['string', Rule::in(array_column(AgeBand::cases(), 'value'))],
            'original_language_code' => ['nullable', 'string', 'max:5'],
            'country_code' => ['nullable', 'string', 'max:2'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $contentItem->assignCategories($validated['category_ids']);
        $contentItem->assignAgeBands($validated['age_bands']);

        $contentItem->update([
            'original_language_code' => $validated['original_language_code'] ?? $contentItem->original_language_code,
            'country_code' => $validated['country_code'] ?? $contentItem->country_code,
            'internal_notes' => $validated['internal_notes'] ?? $contentItem->internal_notes,
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Classification updated.']);
    }
}
