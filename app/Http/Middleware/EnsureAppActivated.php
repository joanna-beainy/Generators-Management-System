<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Licensing\LicenseService;

class EnsureAppActivated
{
    public function handle(Request $request, Closure $next): Response
    {
        $licenseService = app(LicenseService::class);

        // Allow access to the activation route itself to prevent loop
        if ($request->routeIs('activation')) {
            // But if already activated, redirect to home
            if ($licenseService->isActivated()) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        }

        // Check if activated
        if (!$licenseService->isActivated()) {
            return redirect()->route('activation');
        }

        return $next($request);
    }
}
