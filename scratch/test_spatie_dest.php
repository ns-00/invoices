<?php

require 'c:/xampp/htdocs/laravel-invoices-starter/vendor/autoload.php';

$app = require_once 'c:/xampp/htdocs/laravel-invoices-starter/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Backup\BackupDestination\BackupDestination;

$dest = BackupDestination::create('backups', config('backup.backup.name'));
echo "Destination disk: " . $dest->diskName() . "\n";
echo "Backup count: " . $dest->backups()->count() . "\n";
foreach ($dest->backups() as $b) {
    echo "Path: " . $b->path() . "\n";
    echo "Size: " . round($b->sizeInBytes() / 1024 / 1024, 2) . " MB\n";
    echo "Date: " . $b->date()->toDateTimeString() . "\n";
    echo "Exists: " . ($b->exists() ? 'yes' : 'no') . "\n";
}
