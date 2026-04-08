<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStrategicCalendarAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->company_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $company = $user->relationLoaded('company')
            ? $user->company
            : $user->company()->first();

        if (! $company || ! $company->hasStrategicCalendarEnabled()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
