<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->role === UserRole::SuperAdmin) {
            return redirect()->route('admin.dashboard');
        }

        if (! $user->company_id) {
            abort(Response::HTTP_FORBIDDEN, 'Usuário sem empresa vinculada.');
        }

        return $next($request);
    }
}
