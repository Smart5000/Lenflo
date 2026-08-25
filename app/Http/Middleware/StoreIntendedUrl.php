<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreIntendedUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->user()) {
            $redirect = $request->query('redirect');

            if ($redirect && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
                $request->session()->put('url.intended', $redirect);
            }
        }

        return $next($request);
    }
}
