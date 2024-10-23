<?php

namespace App\Http\Middleware;

use App\Exceptions\ForbiddenException;
use Closure;
use Illuminate\Http\Request;

class FirewallMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (config('app.firewall.enabled')) {
            $allowedIps = explode(',', config('app.firewall.ip'));

            if (!in_array($request->ip(), $allowedIps)) {
                throw new ForbiddenException();
            }
        }

        return $next($request);
    }
}
