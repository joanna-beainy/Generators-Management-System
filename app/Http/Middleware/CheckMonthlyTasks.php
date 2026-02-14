<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Models\MeterReading;
use Illuminate\Support\Facades\Log;

class CheckMonthlyTasks
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run on the dashboard GET request and only once per session
        if ($request->isMethod('GET') && $request->routeIs('users.dashboard') && !$request->session()->has('monthly_tasks_checked')) {
            
            $this->executeTasks();
            $request->session()->put('monthly_tasks_checked', true);
        }

        return $next($request);
    }

    private function executeTasks(): void
    {
        try {
            $monthKey = now()->format('Y-m');

            // 1. Automated Backup (Once a month)
            if (Cache::get('last_automated_backup_month') !== $monthKey) {
                $this->runInBackground('db:backup');
                Cache::forever('last_automated_backup_month', $monthKey);
            }

            // 2. Monthly Meter Readings (From 28th onwards)
            if (now()->day >= 28 && !MeterReading::whereDate('reading_for_month', now()->startOfMonth())->exists()) {
                $this->runInBackground('meter:generate-readings');
            }
        } catch (\Exception $e) {
            Log::error("Automated Tasks Failed: " . $e->getMessage());
        }
    }

    private function runInBackground(string $command): void
    {
        $php = escapeshellarg(PHP_BINARY);
        $artisan = escapeshellarg(base_path('artisan'));
        
        pclose(popen("start /B \"\" {$php} {$artisan} {$command}", "r"));
    }
}
