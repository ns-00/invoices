<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ActivityLogRepositoryInterface extends BaseRepositoryInterface
{
    public function getRecent(int $limit = 100, ?int $userId = null): Collection;
}
