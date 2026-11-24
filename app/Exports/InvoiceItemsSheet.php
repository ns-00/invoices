<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceItemsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(protected Collection $items) {}

    public function collection(): Collection
    {
        return $this->items->map(function ($item, $index) {
            return [
                'index'          => $index + 1,
                'invoice_number' => $item->invoice->number ?? '-',
                'project'        => $item->invoice->month->project->name ?? '-',
                'month'          => $item->invoice->month->label ?? '-',
                'item_name'      => $item->item_name,
                'quantity'       => number_format($item->quantity, 2),
                'unit_price'     => number_format($item->unit_price, 2),
                'total_price'    => number_format($item->total_price, 2),
                'notes'          => $item->notes ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'رقم الفاتورة التابع لها',
            'المشروع',
            'الشهر',
            'بيان / اسم السلعة أو الخدمة',
            'الكمية',
            'سعر الوحدة (ر.س)',
            'الإجمالي (ر.س)',
            'ملاحظات',
        ];
    }

    public function title(): string
    {
        return 'سطور وبنود الفواتير';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2C5282'],
                ],
            ],
        ];
    }
}
