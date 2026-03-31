<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->prepareDatabaseSafely();

        Menu::create();

        Window::open()
            ->maximized()
            ->hideMenu();
    }

    private function prepareDatabaseSafely(): void
    {
        $lockPath = storage_path('framework/nativephp-bootstrap.lock');
        $lockDirectory = dirname($lockPath);

        if (!is_dir($lockDirectory)) {
            mkdir($lockDirectory, 0777, true);
        }

        $handle = fopen($lockPath, 'c+');

        if ($handle === false) {
            $this->prepareDatabase();

            return;
        }

        $startedAt = microtime(true);
        $timeoutSeconds = 15;

        $locked = false;

        try {
            while (!flock($handle, LOCK_EX | LOCK_NB)) {
                if ((microtime(true) - $startedAt) >= $timeoutSeconds) {
                    // Another launch is already preparing the database.
                    return;
                }

                usleep(250000);
            }

            $locked = true;
            $this->prepareDatabase();
        } finally {
            if ($locked) {
                flock($handle, LOCK_UN);
            }

            fclose($handle);
        }
    }

    private function prepareDatabase(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('sessions')) {
            Artisan::call('migrate', ['--force' => true]);
        }

        if (User::count() === 0) {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DefaultUserSeeder',
                '--force' => true,
            ]);
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
