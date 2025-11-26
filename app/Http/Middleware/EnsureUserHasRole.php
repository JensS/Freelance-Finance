<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Role values (e.g., 'owner', 'tax_accountant')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // User authentication is handled by the 'auth' middleware
        // This middleware only checks role-based authorization
        $user = $request->user();

        // If specific roles are provided, check against them
        if (! empty($roles)) {
            foreach ($roles as $role) {
                $roleEnum = Role::tryFrom($role);
                if ($roleEnum && $user->hasRole($roleEnum)) {
                    return $next($request);
                }
            }

            abort(403, 'Sie haben keine Berechtigung für diese Aktion.');
        }

        // If no specific roles, just check route access based on user's role
        $routeName = $request->route()?->getName();
        if ($routeName && ! $user->canAccessRoute($routeName)) {
            abort(403, 'Sie haben keine Berechtigung für diese Seite.');
        }

        return $next($request);
    }
}
