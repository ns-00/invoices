<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepo,
        protected ProjectService $projectService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $this->projectRepo->forUser($user->id);

        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بإضافة مشاريع (صلاحية مشاهدة فقط).');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'أدخل اسم المشروع.',
        ]);

        $project = $this->projectService->createProject($user, $data);

        return redirect()->route('projects.show', $project)->with('ok', 'تمت إضافة المشروع بنجاح.');
    }

    public function show(Request $request, Project $project)
    {
        $user = $request->user();
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بعرض هذا المشروع.');

        $project = $this->projectRepo->findWithMonths($project->id, $user->id);

        return view('projects.show', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بتعديل المشاريع (صلاحية مشاهدة فقط).');
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بتعديل هذا المشروع.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'أدخل اسم المشروع.',
        ]);

        $this->projectService->updateProject($project, $data, $user);

        return back()->with('ok', 'تم تعديل اسم المشروع بنجاح.');
    }

    public function destroy(Request $request, Project $project)
    {
        $user = $request->user();
        abort_unless($user->canManageInvoices(), 403, 'غير مصرح لك بحذف المشاريع (صلاحية مشاهدة فقط).');
        abort_unless($project->user_id === $user->id, 403, 'غير مصرح لك بحذف هذا المشروع.');

        $this->projectService->deleteProject($project, $user);

        return redirect()->route('projects.index')->with('ok', 'تم حذف المشروع وجميع ملفاته وفواتيره نهائياً.');
    }
}
