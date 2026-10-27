<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        if (empty($permissions)) {
            return $next($request);
        }

        $allowedPermissions = [];

        foreach ($permissions as $permissionGroup) {
            foreach (explode(',', $permissionGroup) as $permission) {
                $allowedPermissions[] = strtolower(trim($permission));
            }
        }

        $userPermissions = $request->user()?->role?->permissions ?? collect();

        $hasPermission = $userPermissions->contains(
            fn ($permission) => in_array(strtolower((string) $permission->clave), $allowedPermissions, true)
        );

        if ($hasPermission) {
            return $next($request);
        }

        abort(403, 'No tienes el permiso requerido para esta acción.');
    }
}
