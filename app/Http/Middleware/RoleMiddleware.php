<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            abort(401);
        }

        $roles = array_map(
            fn ($role) => Role::tryFrom($role),
            $roles
        );

        if (!in_array($request->user()->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
