<?php

namespace App\Repositories\Eloquent;

use App\Models\Month;
use App\Repositories\Contracts\MonthRepositoryInterface;

class MonthRepository extends BaseRepository implements MonthRepositoryInterface
{
    public function __construct(Month $model)
    {
        parent::__construct($model);
    }

    public function findWithInvoices(int $id, ?int $userId = null): ?Month
    {
        $query = $this->model->where('id', $id)
            ->with([
                'project.user',
                'invoices' => fn ($q) => $q->with('items')->orderBy('invoice_date', 'desc')->orderBy('id', 'desc'),
            ]);

        if ($userId !== null) {
            $query->whereHas('project', fn ($p) => $p->where('user_id', $userId));
        }

        return $query->first();
    }

    public function toggleClosed(int $id): Month
    {
        /** @var Month $month */
        $month = $this->findOrFail($id);
        $month->update(['closed' => ! $month->closed]);

        return $month->fresh();
    }

    public function findByProjectAndYm(int $projectId, string $ym): ?Month
    {
        return $this->model->where('project_id', $projectId)->where('ym', $ym)->first();
    }
}
