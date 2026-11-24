<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    public function forMonth(int $monthId): Collection
    {
        return $this->model->where('month_id', $monthId)
            ->with('items')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function forUser(int $userId): Collection
    {
        return $this->model->whereHas('month.project', fn ($p) => $p->where('user_id', $userId))
            ->with(['month.project', 'items'])
            ->orderBy('invoice_date', 'desc')
            ->get();
    }

    public function getFiltered(array $filters, ?int $userId = null, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['month.project', 'items']);

        if ($userId !== null) {
            $query->whereHas('month.project', fn ($p) => $p->where('user_id', $userId));
        }

        if (! empty($filters['project_id'])) {
            $query->whereHas('month', fn ($m) => $m->where('project_id', $filters['project_id']));
        }

        if (! empty($filters['month_id'])) {
            $query->where('month_id', $filters['month_id']);
        }

        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhereHas('items', fn ($iq) => $iq->where('item_name', 'like', $search));
            });
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('invoice_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('invoice_date', '<=', $filters['to_date']);
        }

        if (isset($filters['paid_status']) && $filters['paid_status'] !== '') {
            if ($filters['paid_status'] === 'paid') {
                $query->where('has_due', true)->where('due_paid', true);
            } elseif ($filters['paid_status'] === 'unpaid') {
                $query->where('has_due', true)->where('due_paid', false);
            } elseif ($filters['paid_status'] === 'has_due') {
                $query->where('has_due', true);
            } elseif ($filters['paid_status'] === 'no_due') {
                $query->where('has_due', false);
            }
        }

        $query->orderBy('invoice_date', 'desc')->orderBy('id', 'desc');

        if ($perPage) {
            return $query->paginate($perPage)->withQueryString();
        }

        return $query->get();
    }

    public function findWithItems(int $id): ?Invoice
    {
        return $this->model->with(['items', 'month.project'])->find($id);
    }

    public function syncItems(int $invoiceId, array $items): void
    {
        $invoice = $this->findOrFail($invoiceId);

        // Delete existing items
        $invoice->items()->delete();

        // Insert new items
        foreach ($items as $item) {
            if (! empty($item['item_name'])) {
                $qty = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $total = (float) ($item['total_price'] ?? ($qty * $unitPrice));

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'item_name'   => $item['item_name'],
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $total,
                    'notes'       => $item['notes'] ?? null,
                ]);
            }
        }
    }

    public function togglePaid(int $id): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = $this->findOrFail($id);

        $newPaid = ! $invoice->due_paid;
        $invoice->update([
            'due_paid'    => $newPaid,
            'due_paid_at' => $newPaid ? now() : null,
        ]);

        return $invoice->fresh();
    }
}
