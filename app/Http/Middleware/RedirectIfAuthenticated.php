<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            $user = Auth::guard($guard)->user();

            if ($user !== null) {
                return redirect()->route(
                    $user instanceof User ? $user->portalHomeRouteName() : 'portal.dashboard',
                );
            }
        }

        return $next($request);
    }
}
