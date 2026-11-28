<?php

require 'c:/xampp/htdocs/laravel-invoices-starter/vendor/autoload.php';

$app = require_once 'c:/xampp/htdocs/laravel-invoices-starter/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BackupService;

$service = app(BackupService::class);
$backups = $service->listBackups();

echo "Total backups found: " . count($backups) . "\n";
foreach ($backups as $b) {
    echo "- " . $b['filename'] . " (" . $b['size_mb'] . " MB) [" . $b['created_at'] . "] " . (file_exists($b['path']) ? 'EXISTS' : 'NOT FOUND') . "\n";
}
