<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogRepositoryInterface $activityRepo
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Admin sees all system activity logs; regular user sees their own
        $userId = $user->isAdmin() ? null : $user->id;
        $activities = $this->activityRepo->getRecent(100, $userId);

        return view('activity-logs.index', compact('activities'));
    }
}
