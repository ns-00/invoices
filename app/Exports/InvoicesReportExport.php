<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesReportExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings
{
    public function __construct(
        protected Collection $invoices,
        protected array $metrics
    ) {}

    public function view(): View
    {
        return view('reports.excel', [
            'invoices' => $this->invoices,
            'metrics'  => $this->metrics,
        ]);
    }

    public function drawings()
    {
        $logoPath = public_path('images/right.png');
        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Rite Group');
        $drawing->setDescription('Rite Group Logo');
        $drawing->setPath($logoPath);
        $drawing->setHeight(34);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(12);
        $drawing->setOffsetY(4);

        return $drawing;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Tajawal');

        // إعدادات الطباعة والعرض في إكسل لتكون A4 بالعرض (Landscape) مطابقة للـ PDF والطباعة
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Tajawal'],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0F5C63'], // Site primary teal
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            2 => [
                'font' => ['size' => 10, 'color' => ['argb' => 'FF0F5C63'], 'name' => 'Tajawal'],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6F0F1'],
                ],
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
            ],
        ];
    }
}
