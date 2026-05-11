<?php

namespace App\Http\Middleware;

use App\Enums\AdminPermissionModule;
use App\Enums\PermissionAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * @param  string  $action  view|create|edit|delete ou "auto" (mapeia pelo método HTTP).
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'auto'): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $moduleEnum = AdminPermissionModule::from($module);

        if ($action === 'auto') {
            $actionEnum = match ($request->method()) {
                'POST' => PermissionAction::Create,
                'PUT', 'PATCH' => PermissionAction::Edit,
                'DELETE' => PermissionAction::Delete,
                default => PermissionAction::View,
            };
        } else {
            $actionEnum = PermissionAction::from($action);
        }

        if (! $user->canAccessAdmin($moduleEnum, $actionEnum)) {
            abort(Response::HTTP_FORBIDDEN, 'Sem permissão para esta área.');
        }

        return $next($request);
    }
}
