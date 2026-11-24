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

        return view('backups.index', compact('backups'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $result = $this->backupService->createBackup($user->name . ' (' . $user->email . ')');

        return back()->with('ok', "تم إنشاء النسخة الاحتياطية بنجاح: {$result['filename']} بحجم ({$result['size_mb']} ميجابايت).");
    }

    public function download(string $filename)
    {
        $safeFilename = basename($filename);
        $path = storage_path('app/backups/' . $safeFilename);

        if (! file_exists($path)) {
            abort(404, 'ملف النسخة الاحتياطية غير موجود.');
        }

        activity()
            ->causedBy(auth()->user())
            ->log("قام بتحميل النسخة الاحتياطية: {$safeFilename}");

        return response()->download($path);
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
