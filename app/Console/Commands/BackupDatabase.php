<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Native\Desktop\Facades\Notification;
use Illuminate\Support\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=30 : Keep last N backups, 0 to keep all}';
    protected $description = 'Backup the SQLite database to the Documents folder';

    public function handle()
    {
        $this->info('🚀 Starting database backup...');

        // 1. Get the database path from the ACTIVE connection
        $connection = config('database.default');
        $dbPath = config("database.connections.{$connection}.database");
        
        // If the path is empty (can happen if config is cleared), fallback to standard
        if (empty($dbPath)) {
            $dbPath = database_path('nativephp.sqlite');
            if (!File::exists($dbPath)) {
                $dbPath = database_path('database.sqlite');
            }
        }
        
        // Ensure path is absolute for File operations
        $isAbsolute = preg_match('/^([a-zA-Z]:\\\\|\\\\|\\/)/', $dbPath);
        if (!$isAbsolute && !empty($dbPath)) {
            $dbPath = base_path($dbPath);
        }
        
        if (!File::exists($dbPath)) {
            $this->error("❌ Database file not found at: {$dbPath}");
            return;
        }

        // 2. Prepare the Documents/GeneratorsBackups folder
        $userProfile = getenv('USERPROFILE') ?: $_SERVER['USERPROFILE'] ?: null;
        if (!$userProfile) {
            $this->error('❌ Could not determine the user profile folder.');
            return;
        }

        $documentsPath = $userProfile . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . 'GeneratorsBackups';

        if (!File::exists($documentsPath)) {
            File::makeDirectory($documentsPath, 0755, true);
        }

        // 3. Create the backup file
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupFile = $documentsPath . DIRECTORY_SEPARATOR . "backup_{$timestamp}.sqlite";

        try {
            // 4. Perform backup (using VACUUM INTO for open database support)
            try {
                \Illuminate\Support\Facades\DB::statement("PRAGMA busy_timeout = 5000");
                \Illuminate\Support\Facades\DB::statement("VACUUM INTO '{$backupFile}'");
            } catch (\Exception $e) {
                // Fallback to direct copy if VACUUM fails
                File::copy($dbPath, $backupFile);
            }

            if (File::exists($backupFile)) {
                $this->info("✅ Backup created successfully at: {$backupFile}");

                // 5. Clean old backups
                $this->cleanOldBackups($documentsPath);

                // 6. Show desktop notification
                $this->showNotification($backupFile);
            }
        } catch (\Exception $e) {
            $this->error("❌ Backup failed: {$e->getMessage()}");
        }
    }

    /**
     * Clean old backups, keep only specified number
     */
    protected function cleanOldBackups(string $backupDir): void
    {
        $keep = (int) $this->option('keep');
        
        if ($keep <= 0) {
            return;
        }
        
        $backupFiles = glob($backupDir . DIRECTORY_SEPARATOR . '*.sqlite');
        
        if (count($backupFiles) <= $keep) {
            return;
        }
        
        usort($backupFiles, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $filesToDelete = array_slice($backupFiles, 0, count($backupFiles) - $keep);
        
        foreach ($filesToDelete as $file) {
            File::delete($file);
            $this->line("🗑️ Deleted old backup: " . basename($file));
        }
    }

    /**
     * Show desktop notification
     */
    protected function showNotification(string $backupFile): void
    {
        try {
            Notification::new()
                ->title('✅ Backup Complete')
                ->message("Database successfully backed up to your Documents folder.")
                ->show();
        } catch (\Exception $e) {
            // Ignore if notifications fail
        }
    }
}
