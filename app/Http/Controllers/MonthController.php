<?php

namespace App\Http\Controllers;

use App\Models\Month;
use App\Models\Project;
use App\Repositories\Contracts\MonthRepositoryInterface;
use App\Services\MonthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonthController extends Controller
{
    public function __construct(
        protected MonthRepositoryInterface $monthRepo,
        protected MonthService $monthService
    ) {}

    public function store(Request $request, Project $project)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بإضافة شهور (صلاحية مشاهدة فقط).');
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بإضافة شهر لهذا المشروع.');

        $data = $request->validate([
            'ym' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
                Rule::unique('months')->where('project_id', $project->id),
            ],
        ], [
            'ym.required' => 'اختر الشهر.',
            'ym.regex'    => 'صيغة الشهر غير صحيحة (YYYY-MM).',
            'ym.unique'   => 'ملف هذا الشهر موجود مسبقاً لهذا المشروع.',
        ]);

        $month = $this->monthService->createMonth($project, $data['ym']);

        return redirect()->route('months.show', [$project, $month])->with('ok', 'تم فتح ملف الشهر بنجاح.');
    }

    public function show(Request $request, Project $project, Month $month)
    {
        abort_unless($month->project_id === $project->id, 404);
        $user = $request->user();
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بعرض هذا الشهر.');

        $month = $this->monthRepo->findWithInvoices($month->id, $user->id);

        return view('months.show', compact('project', 'month'));
    }

    public function toggleClosed(Request $request, Month $month)
    {
        $project = $month->project;
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بإقفال/فتح الشهور (صلاحية مشاهدة فقط).');
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بتغيير حالة هذا الشهر.');

        $updated = $this->monthService->toggleClosed($month);

        $msg = $updated->closed ? 'تم إقفال الشهر.' : 'تمت إعادة فتح الشهر.';

        // عند الإقفال، نفتح الشهر التالي تلقائياً إن لم يكن موجوداً
        if ($updated->closed) {
            [$y, $m] = explode('-', $updated->ym);
            $y = (int) $y;
            $m = (int) $m;
            $nextYm = ($m === 12) ? (($y + 1).'-01') : sprintf('%04d-%02d', $y, $m + 1);

            if (! $project->months()->where('ym', $nextYm)->exists()) {
                $newMonth = $this->monthService->createMonth($project, $nextYm);
                $msg = 'تم الإقفال وفُتح ملف '.$newMonth->label.'.';
            }
        }

        return back()->with('ok', $msg);
    }

    public function destroy(Request $request, Month $month)
    {
        $project = $month->project;
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بحذف الشهور (صلاحية مشاهدة فقط).');
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بحذف هذا الشهر.');

        $this->monthService->deleteMonth($month);

        return redirect()->route('projects.show', $project)->with('ok', 'تم حذف ملف الشهر وجميع فواتيره نهائياً.');
    }
}
