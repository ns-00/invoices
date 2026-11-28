<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupService
{
    protected string $diskName = 'backups';
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a backup archive using Spatie Laravel Backup.
     */
    public function createBackup(string $triggeredBy = 'System'): array
    {
        // When running tests with in-memory SQLite, dump is skipped as in-memory SQLite cannot be dumped via CLI
        $isInMemorySqlite = config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:';

        $options = $isInMemorySqlite ? ['--only-files' => true] : [];

        $exitCode = Artisan::call('backup:run', $options);

        if ($exitCode !== 0) {
            $output = trim(Artisan::output());
            throw new \RuntimeException('فشل تنفيذ النسخ الاحتياطي عبر spatie/laravel-backup: ' . $output);
        }

        $destination = $this->getBackupDestination();
        /** @var Backup|null $newestBackup */
        $newestBackup = $destination->newestBackup();

        $filename = $newestBackup ? basename($newestBackup->path()) : 'backup-' . now()->format('Y-m-d-His') . '.zip';
        $fullPath = $newestBackup ? storage_path('app/backups/' . $newestBackup->path()) : $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        $sizeMb = $newestBackup ? round($newestBackup->sizeInBytes() / 1024 / 1024, 2) : 0;
        $createdAt = $newestBackup ? $newestBackup->date()->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');

        activity()
            ->causedBy(auth()->user())
            ->log("تم إنشاء نسخة احتياطية جديدة بنجاح عبر Spatie Backup: {$filename} ({$sizeMb} ميجابايت) - المصدر: {$triggeredBy}");

        return [
            'success'    => true,
            'filename'   => $filename,
            'path'       => $fullPath,
            'size_mb'    => $sizeMb,
            'created_at' => $createdAt,
        ];
    }

    /**
     * List all existing backup archives from Spatie backup destination.
     */
    public function listBackups(): array
    {
        $destination = $this->getBackupDestination();
        $backups = [];
        $recordedFiles = [];

        // 1. Spatie Backups
        if ($destination->isReachable()) {
            foreach ($destination->backups() as $b) {
                if ($b->exists()) {
                    $filename = basename($b->path());
                    $recordedFiles[$filename] = true;

                    $backups[] = [
                        'filename'   => $filename,
                        'rel_path'   => $b->path(),
                        'path'       => storage_path('app/backups/' . $b->path()),
                        'size_mb'    => round($b->sizeInBytes() / 1024 / 1024, 2),
                        'created_at' => $b->date()->format('Y-m-d H:i:s'),
                        'is_spatie'  => true,
                    ];
                }
            }
        }

        // 2. Legacy / Root directory backups for backwards compatibility
        $rootFiles = File::files($this->backupDir);
        foreach ($rootFiles as $file) {
            if ($file->getExtension() === 'zip') {
                $filename = $file->getFilename();
                if (! isset($recordedFiles[$filename])) {
                    $backups[] = [
                        'filename'   => $filename,
                        'rel_path'   => $filename,
                        'path'       => $file->getRealPath(),
                        'size_mb'    => round($file->getSize() / 1024 / 1024, 2),
                        'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
                        'is_spatie'  => false,
                    ];
                }
            }
        }

        // Sort latest first
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Find the full path of a backup by filename safely.
     */
    public function findBackupPath(string $filename): ?string
    {
        $safeName = basename($filename);
        $appName = config('backup.backup.name', 'invoices-backup');

        // Check inside Spatie app directory
        $spatiePath = $this->backupDir . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . $safeName;
        if (File::exists($spatiePath)) {
            return $spatiePath;
        }

        // Check inside root backup directory
        $rootPath = $this->backupDir . DIRECTORY_SEPARATOR . $safeName;
        if (File::exists($rootPath)) {
            return $rootPath;
        }

        return null;
    }

    /**
     * Delete an existing backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->findBackupPath($filename);

        if ($path && File::exists($path)) {
            $safeName = basename($path);
            activity()
                ->causedBy(auth()->user())
                ->log("قام بحذف النسخة الاحتياطية: {$safeName}");

            return File::delete($path);
        }

        return false;
    }

    /**
     * Clean old backups using Spatie clean command.
     */
    public function cleanOldBackups(int $days = 14): void
    {
        Artisan::call('backup:clean');

        // Clean legacy loose files older than $days
        $threshold = now()->subDays($days)->timestamp;
        $files = File::files($this->backupDir);

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip' && $file->getMTime() < $threshold) {
                File::delete($file->getRealPath());
            }
        }
    }

    /**
     * Get Spatie Backup destination instance.
     */
    public function getBackupDestination(): BackupDestination
    {
        $appName = config('backup.backup.name', 'invoices-backup');

        return BackupDestination::create($this->diskName, $appName);
    }
}
