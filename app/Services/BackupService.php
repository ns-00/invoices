<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use ZipArchive;

class BackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a complete backup (database SQL dump + uploaded files) into a ZIP archive.
     */
    public function createBackup(string $triggeredBy = 'System'): array
    {
        $timestamp = now()->format('Y-m-d-His');
        $zipFileName = "backup-{$timestamp}.zip";
        $zipFilePath = $this->backupDir . DIRECTORY_SEPARATOR . $zipFileName;

        // 1. Generate Database SQL Dump using PDO
        $sqlDump = $this->generateDatabaseDump();
        $tempSqlFile = storage_path("app/temp_dump_{$timestamp}.sql");
        File::put($tempSqlFile, $sqlDump);

        // 2. Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add SQL dump
            $zip->addFile($tempSqlFile, 'database.sql');

            // Add uploaded files from storage/app/public
            $publicStoragePath = storage_path('app/public');
            if (File::isDirectory($publicStoragePath)) {
                $files = File::allFiles($publicStoragePath);
                foreach ($files as $file) {
                    $relativePath = 'uploads/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }

            // Add metadata
            $metadata = [
                'created_at'   => now()->toDateTimeString(),
                'triggered_by' => $triggeredBy,
                'app_name'     => config('app.name'),
                'database'     => config('database.connections.mysql.database'),
                'php_version'  => PHP_VERSION,
            ];
            $zip->addFromString('metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $zip->close();
        }

        // Cleanup temporary SQL file
        if (File::exists($tempSqlFile)) {
            File::delete($tempSqlFile);
        }

        // 3. Clean old backups (keep last 14 days)
        $this->cleanOldBackups(14);

        $fileSize = File::exists($zipFilePath) ? File::size($zipFilePath) : 0;

        activity()
            ->causedBy(auth()->user())
            ->log("تم إنشاء نسخة احتياطية جديدة بنجاح: {$zipFileName} (" . round($fileSize / 1024 / 1024, 2) . " ميجابايت)");

        return [
            'success'   => true,
            'filename'  => $zipFileName,
            'path'      => $zipFilePath,
            'size_kb'   => round($fileSize / 1024, 2),
            'size_mb'   => round($fileSize / 1024 / 1024, 2),
            'timestamp' => $timestamp,
        ];
    }

    /**
     * List all existing backup archives.
     */
    public function listBackups(): array
    {
        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $backups[] = [
                    'filename'   => $file->getFilename(),
                    'path'       => $file->getRealPath(),
                    'size_mb'    => round($file->getSize() / 1024 / 1024, 2),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
                ];
            }
        }

        // Sort latest first
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Delete an existing backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $safeFilename = basename($filename);
        $path = $this->backupDir . DIRECTORY_SEPARATOR . $safeFilename;

        if (File::exists($path)) {
            activity()
                ->causedBy(auth()->user())
                ->log("قام بحذف النسخة الاحتياطية: {$safeFilename}");

            return File::delete($path);
        }

        return false;
    }

    /**
     * Delete backups older than $days.
     */
    public function cleanOldBackups(int $days = 14): void
    {
        $threshold = now()->subDays($days)->timestamp;
        $files = File::files($this->backupDir);

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip' && $file->getMTime() < $threshold) {
                File::delete($file->getRealPath());
            }
        }
    }

    /**
     * Generate complete MySQL SQL Dump using PDO.
     */
    protected function generateDatabaseDump(): string
    {
        /** @var PDO $pdo */
        $pdo = DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        $dbName = DB::connection()->getDatabaseName();

        $output = "-- ====================================================\n";
        $output .= "-- Laravel Invoices System Database Backup\n";
        $output .= "-- Driver: {$driver} | Database: {$dbName}\n";
        $output .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $output .= "-- ====================================================\n\n";

        if ($driver === 'sqlite') {
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='{$table}'");
                $createSql = $stmt->fetchColumn();
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                if ($createSql) {
                    $output .= $createSql . ";\n\n";
                }

                $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                if (! empty($rows)) {
                    $output .= "-- Dumping data for table `{$table}`\n";
                    foreach ($rows as $dataRow) {
                        $columns = array_map(fn ($col) => "`{$col}`", array_keys($dataRow));
                        $values = array_map(fn ($val) => $val === null ? 'NULL' : $pdo->quote((string) $val), array_values($dataRow));
                        $output .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }

            return $output;
        }

        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // Fetch all tables
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Drop & Create statement
            $output .= "-- ----------------------------------------------------\n";
            $output .= "-- Table structure for `{$table}`\n";
            $output .= "-- ----------------------------------------------------\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $output .= ($row[1] ?? '') . ";\n\n";

            // Insert rows
            $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            if (! empty($rows)) {
                $output .= "-- Dumping data for table `{$table}`\n";
                foreach ($rows as $dataRow) {
                    $columns = array_map(fn ($col) => "`{$col}`", array_keys($dataRow));
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return $pdo->quote((string) $val);
                    }, array_values($dataRow));

                    $output .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $output;
    }
}
