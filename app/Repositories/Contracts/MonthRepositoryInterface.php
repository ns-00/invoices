<?php

namespace App\Repositories\Contracts;

use App\Models\Month;

interface MonthRepositoryInterface extends BaseRepositoryInterface
{
    public function findWithInvoices(int $id, ?int $userId = null): ?Month;

    public function toggleClosed(int $id): Month;

    public function findByProjectAndYm(int $projectId, string $ym): ?Month;
}
