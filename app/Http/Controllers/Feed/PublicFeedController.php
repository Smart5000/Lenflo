<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PublicFeedController extends Controller
{
    /**
     * Display the public learning feed.
     */
    public function index()
    {
        return Inertia::render('feed/index', [
            'meta' => [
                'title' => 'Feed',
                'description' => 'Discover age-relevant learning content.',
            ],
        ]);
    }
}
