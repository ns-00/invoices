<?php

namespace App\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface extends BaseRepositoryInterface
{
    public function forUser(int $userId): Collection;

    public function findWithMonths(int $id, ?int $userId = null): ?Project;
}
