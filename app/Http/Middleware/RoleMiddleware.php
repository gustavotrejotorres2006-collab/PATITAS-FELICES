<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $user = $request->user();
        $userRole = $user?->role;
        $allowedRoles = [];

        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $allowedRole) {
                $allowedRoles[] = strtolower(trim($allowedRole));
            }
        }

        if (
            $user?->estado === 'activo'
            && $userRole?->activo
            && in_array(strtolower($userRole->nombre), $allowedRoles, true)
        ) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
