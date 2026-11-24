<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRepository extends BaseRepository implements ActivityLogRepositoryInterface
{
    public function __construct(Activity $model)
    {
        parent::__construct($model);
    }

    public function getRecent(int $limit = 100, ?int $userId = null): Collection
    {
        $query = $this->model->with(['causer', 'subject'])->latest();

        if ($userId !== null) {
            $query->where('causer_id', $userId);
        }

        return $query->take($limit)->get();
    }
}
