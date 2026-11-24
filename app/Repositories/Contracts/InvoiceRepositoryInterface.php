<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function forMonth(int $monthId): Collection;

    public function forUser(int $userId): Collection;

    public function getFiltered(array $filters, ?int $userId = null, ?int $perPage = null): Collection|LengthAwarePaginator;

    public function findWithItems(int $id): ?Invoice;

    public function syncItems(int $invoiceId, array $items): void;

    public function togglePaid(int $id): Invoice;
}
