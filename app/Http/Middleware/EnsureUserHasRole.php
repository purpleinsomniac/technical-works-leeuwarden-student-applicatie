<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Blokkeer dashboards waarvoor de gebruiker geen rol heeft.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user()?->canUseRole($role), 403);

        return $next($request);
    }
}
