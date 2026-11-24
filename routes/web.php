<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isAdmin() ? 'dashboard' : 'projects.index');
    }

    return redirect()->route('login');
});

// الدخول متاح للضيوف واستعادة كلمة المرور
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    // استعادة كلمة المرور عبر رمز الإيميل
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// كل ما عدا صفحة الدخول يتطلب تسجيل الدخول وحساباً نشطاً
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // تعديل البيانات الشخصية وتغيير كلمة المرور برمز التحقق
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/send-code', [ProfileController::class, 'sendPasswordCode'])->name('profile.send-code');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // صفحة التقارير الشاملة (رؤوس الفواتير والسطور والتصدير)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');

    // إدارة المشاريع والملفات الشهرية والفواتير (صفحة المستخدم الأساسية)
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/months', [MonthController::class, 'store'])->name('projects.months.store');
    Route::get('projects/{project}/months/{month}', [MonthController::class, 'show'])->name('months.show');
    Route::post('months/{month}/toggle', [MonthController::class, 'toggleClosed'])->name('months.toggle');
    Route::delete('months/{month}', [MonthController::class, 'destroy'])->name('months.destroy');

    // إدارة الفواتير
    Route::post('months/{month}/invoices', [InvoiceController::class, 'store'])->name('months.invoices.store');
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/toggle-paid', [InvoiceController::class, 'togglePaid'])->name('invoices.toggle-paid');

    // لوحة التحكم (خاصة بالمدير، وتُعيد توجيه المستخدم العادي إلى المشاريع تلقائياً)
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // خاص بالمدير فقط: إدارة المستخدمين، النسخ الاحتياطية، وسجل الأحداث
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');

        // سجل الأحداث والنشاطات
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // إدارة النسخ الاحتياطية
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});
