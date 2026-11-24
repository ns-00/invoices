<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackupCommand extends Command
{
    protected $signature = 'app:backup-run {--source=CLI : Source or initiator of backup}';

    protected $description = 'Create a full backup of database and uploaded files to storage/app/backups';

    public function handle(BackupService $backupService): int
    {
        $this->info('Starting backup process...');

        try {
            $source = $this->option('source') ?: 'Scheduled Task';
            $result = $backupService->createBackup($source);

            $this->info("Backup created successfully: {$result['filename']}");
            $this->line("File size: {$result['size_mb']} MB");
            $this->line("Saved to: {$result['path']}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Backup failed: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
