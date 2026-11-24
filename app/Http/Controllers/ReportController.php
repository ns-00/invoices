<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected ProjectRepositoryInterface $projectRepo
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['project_id', 'month_id', 'search', 'from_date', 'to_date', 'paid_status']);

        $data = $this->reportService->getReportData($filters, $user);

        // Fetch user projects for the filter dropdown
        $projects = $this->projectRepo->forUser($user->id);

        return view('reports.index', array_merge($data, [
            'projects' => $projects,
        ]));
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['project_id', 'month_id', 'search', 'from_date', 'to_date', 'paid_status']);

        return $this->reportService->exportPdf($filters, $user);
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['project_id', 'month_id', 'search', 'from_date', 'to_date', 'paid_status']);

        return $this->reportService->exportExcel($filters, $user);
    }
}
