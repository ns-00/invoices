<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceHeadersSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(protected Collection $invoices) {}

    public function collection(): Collection
    {
        return $this->invoices->map(function ($inv, $index) {
            return [
                'index'        => $index + 1,
                'number'       => $inv->number,
                'project'      => $inv->month->project->name ?? '-',
                'month'        => $inv->month->label ?? '-',
                'date'         => $inv->invoice_date?->format('Y-m-d') ?? '-',
                'amount'       => number_format($inv->amount, 2),
                'tax'          => number_format($inv->tax, 2),
                'total'        => number_format($inv->total, 2),
                'has_due'      => $inv->has_due ? 'نعم (5%)' : 'لا',
                'due_amount'   => $inv->has_due ? number_format($inv->due_amount, 2) : '0.00',
                'due_date'     => $inv->due_date?->format('Y-m-d') ?? '-',
                'paid_status'  => $inv->due_paid ? 'مسدد' : 'غير مسدد',
                'description'  => $inv->description ?? '-',
                'has_image'    => $inv->image_path ? 'مرفقة' : 'لا يوجد',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'رقم الفاتورة',
            'المشروع',
            'الشهر',
            'تاريخ الفاتورة',
            'المبلغ الخاضع (ر.س)',
            'الضريبة 15% (ر.س)',
            'الإجمالي شامل الضريبة (ر.س)',
            'هل يوجد مستحق 5%',
            'مبلغ المستحق (ر.س)',
            'تاريخ الاستحقاق',
            'حالة السداد',
            'البيان / التفاصيل',
            'مرفق الصورة',
        ];
    }

    public function title(): string
    {
        return 'رؤوس الفواتير';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A365D'], // Deep blue
                ],
            ],
        ];
    }
}
