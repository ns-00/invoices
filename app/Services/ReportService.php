<?php

namespace App\Services;

use App\Exports\InvoicesReportExport;
use App\Models\Invoice;
use App\Models\User;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected ProjectRepositoryInterface $projectRepo
    ) {}

    /**
     * Get filtered report data including summary metrics and invoice headers.
     */
    public function getReportData(array $filters, User $user): array
    {
        // Scope report strictly to the authenticated user's own data (even for administrators)
        $userId = $user->id;

        /** @var Collection $invoices */
        $invoices = $this->invoiceRepo->getFiltered($filters, $userId);

        // Calculate summary KPI metrics
        $totalAmount = (float) $invoices->sum('amount');
        $totalTax = (float) $invoices->sum(fn ($i) => $i->tax);
        $grandTotal = (float) $invoices->sum(fn ($i) => $i->total);
        $totalDue = (float) $invoices->sum(fn ($i) => $i->due_amount);
        $totalPaidDue = (float) $invoices->where('due_paid', true)->sum(fn ($i) => $i->due_amount);
        $totalUnpaidDue = (float) $invoices->where('has_due', true)->where('due_paid', false)->sum(fn ($i) => $i->due_amount);

        $metrics = [
            'total_amount'     => $totalAmount,
            'total_tax'        => $totalTax,
            'grand_total'      => $grandTotal,
            'total_due'        => $totalDue,
            'total_paid_due'   => $totalPaidDue,
            'total_unpaid_due' => $totalUnpaidDue,
            'count_invoices'   => $invoices->count(),
        ];

        return [
            'invoices' => $invoices,
            'metrics'  => $metrics,
            'filters'  => $filters,
        ];
    }

    /**
     * Export report data as Excel matching the site design.
     */
    public function exportExcel(array $filters, User $user): BinaryFileResponse
    {
        $data = $this->getReportData($filters, $user);
        $fileName = 'invoices-report-' . now()->format('Y-m-d-His') . '.xlsx';

        activity()
            ->causedBy($user)
            ->log("قام بتصدير تقرير الفواتير إلى ملف Excel ({$fileName})");

        return Excel::download(new InvoicesReportExport($data['invoices'], $data['metrics']), $fileName);
    }

    /**
     * Export report data as PDF matching the site design exactly.
     */
    public function exportPdf(array $filters, User $user)
    {
        $data = $this->getReportData($filters, $user);
        $fileName = 'invoices-report-' . now()->format('Y-m-d-His') . '.pdf';

        activity()
            ->causedBy($user)
            ->log("قام بتصدير تقرير الفواتير إلى ملف PDF ({$fileName})");

        $sarPath = public_path('images/sar.png');
        $sarBase64 = file_exists($sarPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($sarPath)) : '';

        $logoPath = public_path('images/right.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        if (!is_dir(storage_path('fonts'))) {
            mkdir(storage_path('fonts'), 0755, true);
        }

        $tajawalRegular = str_replace('\\', '/', public_path('fonts/Tajawal-Regular.ttf'));
        $tajawalBold = str_replace('\\', '/', public_path('fonts/Tajawal-Bold.ttf'));

        $arabic = new \ArPHP\I18N\Arabic();
        $ar = function (?string $text) use ($arabic) {
            if ($text === null || $text === '') return '';
            return $arabic->utf8Glyphs((string)$text, 500, false);
        };

        $pdf = Pdf::loadView('reports.pdf', array_merge($data, [
            'sarBase64'      => $sarBase64,
            'logoBase64'     => $logoBase64,
            'tajawalRegular' => $tajawalRegular,
            'tajawalBold'    => $tajawalBold,
            'ar'             => $ar,
        ]));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($fileName);
    }
}
