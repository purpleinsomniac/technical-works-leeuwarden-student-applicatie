<?php

namespace App\Http\Middleware;

use App\Models\ApplicationSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! ApplicationSetting::current()->maintenance_mode
            || $request->user()?->canUseRole('beheerder')
            || $request->routeIs('logout')
        ) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [], 503, ['Retry-After' => '300']);
    }
}
