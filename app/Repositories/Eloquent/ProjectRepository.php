<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    public function forUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['months' => fn ($q) => $q->orderBy('ym', 'desc')])
            ->orderBy('name')
            ->get();
    }

    public function findWithMonths(int $id, ?int $userId = null): ?Project
    {
        $query = $this->model->where('id', $id)->with(['months' => fn ($q) => $q->orderBy('ym', 'desc')]);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }
}
