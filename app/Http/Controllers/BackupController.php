<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {}

    public function index()
    {
        $backups = $this->backupService->listBackups();
        $destination = $this->backupService->getBackupDestination();

        $stats = [
            'total_backups' => count($backups),
            'used_mb'       => round($destination->usedStorage() / 1024 / 1024, 2),
            'is_healthy'    => $destination->isReachable(),
            'newest'        => $destination->newestBackup()?->date()?->format('Y-m-d H:i') ?? '—',
        ];

        return view('backups.index', compact('backups', 'stats'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $source = $user ? "المستخدم: {$user->name} ({$user->email})" : 'لوحة الإدارة';

        try {
            $result = $this->backupService->createBackup($source);

            return back()->with('ok', "تم إنشاء النسخة الاحتياطية بنجاح عبر Spatie Backup: {$result['filename']} بحجم ({$result['size_mb']} ميجابايت).");
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage()]);
        }
    }

    public function download(string $filename)
    {
        $safeFilename = basename($filename);
        $path = $this->backupService->findBackupPath($safeFilename);

        if (! $path || ! file_exists($path)) {
            abort(404, 'ملف النسخة الاحتياطية غير موجود.');
        }

        activity()
            ->causedBy(auth()->user())
            ->log("قام بتحميل النسخة الاحتياطية: {$safeFilename}");

        return response()->download($path, $safeFilename);
    }

    public function destroy(string $filename)
    {
        $deleted = $this->backupService->deleteBackup($filename);

        if ($deleted) {
            return back()->with('ok', 'تم حذف النسخة الاحتياطية بنجاح.');
        }

        return back()->withErrors(['backup' => 'تعذر العثور على النسخة الاحتياطية المحددة أو حذفها.']);
    }
}
