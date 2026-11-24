<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Month;
use App\Models\User;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\MonthRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected MonthRepositoryInterface $monthRepo,
        protected ImageService $imageService
    ) {}

    /**
     * Create an invoice under a month, with optional image and line items.
     */
    public function createInvoice(Month $month, array $data, ?UploadedFile $image = null, array $items = []): Invoice
    {
        if ($month->closed) {
            throw new HttpException(422, 'لا يمكن إضافة فواتير في شهر مغلق.');
        }

        return DB::transaction(function () use ($month, $data, $image, $items) {
            $imagePath = null;
            if ($image) {
                $imagePath = $this->imageService->uploadAndConvertToWebp($image, 'invoices');
            }

            $invoice = $this->invoiceRepo->create([
                'month_id'     => $month->id,
                'number'       => $data['number'],
                'description'  => $data['description'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'amount'       => $data['amount'],
                'has_due'      => ! empty($data['has_due']),
                'due_date'     => ! empty($data['has_due']) ? ($data['due_date'] ?? null) : null,
                'due_paid'     => false,
                'image_path'   => $imagePath,
            ]);

            // Save line items if provided
            if (! empty($items)) {
                $this->invoiceRepo->syncItems($invoice->id, $items);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->log("قام المستخدم بإضافة الفاتورة رقم: {$invoice->number} بمبلغ {$invoice->amount} ر.س");

            return $invoice;
        });
    }

    /**
     * Update an invoice, with optional new image replacement and line items.
     */
    public function updateInvoice(Invoice $invoice, array $data, ?UploadedFile $image = null, ?array $items = null): Invoice
    {
        if ($invoice->month->closed) {
            throw new HttpException(422, 'لا يمكن تعديل فواتير في شهر مغلق.');
        }

        return DB::transaction(function () use ($invoice, $data, $image, $items) {
            $updateData = [
                'number'       => $data['number'],
                'description'  => $data['description'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'amount'       => $data['amount'],
                'has_due'      => ! empty($data['has_due']),
                'due_date'     => ! empty($data['has_due']) ? ($data['due_date'] ?? null) : null,
            ];

            if ($image) {
                // Delete old image if exists
                if ($invoice->image_path) {
                    $this->imageService->deleteImage($invoice->image_path);
                }
                $updateData['image_path'] = $this->imageService->uploadAndConvertToWebp($image, 'invoices');
            }

            $updated = $this->invoiceRepo->update($invoice->id, $updateData);

            if ($items !== null) {
                $this->invoiceRepo->syncItems($updated->id, $items);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($updated)
                ->log("قام المستخدم بتعديل الفاتورة رقم: {$updated->number}");

            return $updated;
        });
    }

    /**
     * Delete an invoice and its associated image.
     */
    public function deleteInvoice(Invoice $invoice): bool
    {
        if ($invoice->month->closed) {
            throw new HttpException(422, 'لا يمكن حذف فواتير من شهر مغلق.');
        }

        if ($invoice->image_path) {
            $this->imageService->deleteImage($invoice->image_path);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->log("قام المستخدم بحذف الفاتورة رقم: {$invoice->number}");

        return $this->invoiceRepo->delete($invoice->id);
    }

    /**
     * Toggle invoice due paid status.
     */
    public function togglePaid(Invoice $invoice): Invoice
    {
        $updated = $this->invoiceRepo->togglePaid($invoice->id);

        $statusText = $updated->due_paid ? 'تم سداد المستحق' : 'تم إلغاء سداد المستحق';
        activity()
            ->causedBy(auth()->user())
            ->performedOn($updated)
            ->log("{$statusText} للفاتورة رقم: {$updated->number}");

        return $updated;
    }
}
