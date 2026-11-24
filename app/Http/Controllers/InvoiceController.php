<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Month;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function store(Request $request, Month $month)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بإضافة فواتير (صلاحية مشاهدة فقط).');
        abort_unless($month->project->user_id === $user->id, 403, 'غير مصرح لك بإضافة فاتورة في هذا الشهر.');

        if ($month->closed) {
            return back()->withErrors(['invoice' => 'الشهر مُقفل — أعد فتحه لإضافة فواتير.']);
        }

        $data = $request->validate([
            'number'              => ['required', 'string', 'max:100'],
            'invoice_date'        => ['required', 'date'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'has_due'             => ['nullable', 'boolean'],
            'due_date'            => ['nullable', 'required_if:has_due,1', 'date'],
            'image'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Max 5MB
            'items'               => ['nullable', 'array'],
            'items.*.item_name'   => ['nullable', 'string', 'max:255'],
            'items.*.quantity'    => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'       => ['nullable', 'string', 'max:255'],
        ], [
            'number.required'       => 'أدخل رقم الفاتورة.',
            'invoice_date.required' => 'أدخل تاريخ الفاتورة.',
            'amount.required'       => 'أدخل المبلغ الخاضع.',
            'amount.min'            => 'يجب أن يكون المبلغ أكبر من صفر.',
            'due_date.required_if'  => 'حدد تاريخ الاستحقاق عند تفعيل المستحق 5%.',
            'image.image'           => 'الملف المرفق يجب أن يكون صورة.',
            'image.max'             => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت (5MB).',
            'image.mimes'           => 'الصيغ المسموح بها للصور: JPEG, PNG, JPG, GIF, WEBP.',
        ]);

        $this->invoiceService->createInvoice(
            $month,
            $data,
            $request->file('image'),
            $data['items'] ?? []
        );

        return back()->with('ok', 'تمت إضافة الفاتورة بنجاح وتحويل الصورة لصيغة WebP.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بتعديل الفواتير (صلاحية مشاهدة فقط).');
        abort_unless($invoice->month->project->user_id === $user->id, 403, 'غير مصرح لك بتعديل هذه الفاتورة.');

        if ($invoice->month->closed) {
            return back()->withErrors(['invoice' => 'الشهر مُقفل — أعد فتحه للتعديل.']);
        }

        $data = $request->validate([
            'number'              => ['required', 'string', 'max:100'],
            'invoice_date'        => ['required', 'date'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'has_due'             => ['nullable', 'boolean'],
            'due_date'            => ['nullable', 'required_if:has_due,1', 'date'],
            'image'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Max 5MB
            'items'               => ['nullable', 'array'],
            'items.*.item_name'   => ['nullable', 'string', 'max:255'],
            'items.*.quantity'    => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'       => ['nullable', 'string', 'max:255'],
        ], [
            'number.required'       => 'أدخل رقم الفاتورة.',
            'invoice_date.required' => 'أدخل تاريخ الفاتورة.',
            'amount.required'       => 'أدخل المبلغ الخاضع.',
            'due_date.required_if'  => 'حدد تاريخ الاستحقاق.',
            'image.max'             => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت (5MB).',
        ]);

        $this->invoiceService->updateInvoice(
            $invoice,
            $data,
            $request->file('image'),
            $data['items'] ?? null
        );

        return back()->with('ok', 'تم تحديث بيانات الفاتورة بنجاح.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بحذف الفواتير (صلاحية مشاهدة فقط).');
        abort_unless($invoice->month->project->user_id === $user->id, 403, 'غير مصرح لك بحذف هذه الفاتورة.');

        if ($invoice->month->closed) {
            return back()->withErrors(['invoice' => 'الشهر مُقفل — أعد فتحه للحذف.']);
        }

        $this->invoiceService->deleteInvoice($invoice);

        return back()->with('ok', 'تم حذف الفاتورة بنجاح.');
    }

    public function togglePaid(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بتغيير حالة السداد (صلاحية مشاهدة فقط).');
        abort_unless($invoice->month->project->user_id === $user->id, 403, 'غير مصرح لك.');

        if (! $invoice->has_due) {
            return back()->withErrors(['invoice' => 'هذه الفاتورة ليس لها مبلغ مستحق.']);
        }

        $updated = $this->invoiceService->togglePaid($invoice);

        if ($request->wantsJson()) {
            return response()->json([
                'ok'       => true,
                'due_paid' => $updated->due_paid,
                'message'  => $updated->due_paid ? 'تم استحقاق المبلغ' : 'لم يتم الاستحقاق بعد',
            ]);
        }

        return back()->with('ok', $updated->due_paid ? 'تم تأكيد استحقاق المبلغ بنجاح.' : 'تم تعديل حالة الاستحقاق.');
    }
}
