<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->is_admin) {
            return $next($request);
        }

        abort(403, 'Access denied');
    }
}
