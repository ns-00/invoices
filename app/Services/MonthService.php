<?php

namespace App\Services;

use App\Models\Month;
use App\Models\Project;
use App\Repositories\Contracts\MonthRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MonthService
{
    public function __construct(
        protected MonthRepositoryInterface $monthRepo
    ) {}

    public function createMonth(Project $project, string $ym): Month
    {
        $existing = $this->monthRepo->findByProjectAndYm($project->id, $ym);
        if ($existing) {
            throw new HttpException(422, 'تم فتح هذا الشهر مسبقاً لهذا المشروع.');
        }

        $month = $this->monthRepo->create([
            'project_id' => $project->id,
            'ym'         => $ym,
            'closed'     => false,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($month)
            ->log("قام بفتح ملف شهر جديد ({$month->label}) لمشروع: {$project->name}");

        return $month;
    }

    public function toggleClosed(Month $month): Month
    {
        $updated = $this->monthRepo->toggleClosed($month->id);

        $actionText = $updated->closed ? 'إغلاق الشهر' : 'إعادة فتح الشهر';
        activity()
            ->causedBy(auth()->user())
            ->performedOn($updated)
            ->log("قام بـ ({$actionText}) لملف: {$updated->label}");

        return $updated;
    }

    public function deleteMonth(Month $month): bool
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($month)
            ->log("قام بحذف ملف الشهر: {$month->label}");

        return $this->monthRepo->delete($month->id);
    }
}
