<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && $userLocale = auth()->user()->locale) {
            app()->setLocale($userLocale);
        } elseif ($sessionLocale = session('locale')) {
            app()->setLocale($sessionLocale);
        }

        return $next($request);
    }
}
