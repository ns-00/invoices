<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepo
    ) {}

    public function createProject(User $user, array $data): Project
    {
        $project = $this->projectRepo->create([
            'user_id' => $user->id,
            'name'    => $data['name'],
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($project)
            ->log("قام بإنشاء المشروع: {$project->name}");

        return $project;
    }

    public function updateProject(Project $project, array $data, ?User $user = null): Project
    {
        $updated = $this->projectRepo->update($project->id, [
            'name' => $data['name'],
        ]);

        activity()
            ->causedBy($user ?? auth()->user())
            ->performedOn($updated)
            ->log("قام بتعديل اسم المشروع إلى: {$updated->name}");

        return $updated;
    }

    public function deleteProject(Project $project, ?User $user = null): bool
    {
        activity()
            ->causedBy($user ?? auth()->user())
            ->performedOn($project)
            ->log("قام بحذف المشروع: {$project->name}");

        return $this->projectRepo->delete($project->id);
    }
}
