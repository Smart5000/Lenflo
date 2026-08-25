<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content\ContentItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function index(Request $request): Response
    {
        $contentItems = ContentItem::query()
            ->with(['categories', 'reviewer'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/content/index', [
            'contentItems' => $contentItems,
        ]);
    }

    public function show(ContentItem $contentItem): Response
    {
        $contentItem->load(['categories', 'reviewer']);

        return Inertia::render('admin/content/show', [
            'contentItem' => $contentItem,
        ]);
    }
}
