<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Month;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return redirect()->route('projects.index');
        }

        $rate = config('invoices.due_rate');

        // مجموع المستحق مع تقريب كل فاتورة لخانتين، ليطابق ما يظهر في الجدول
        $dueSum = fn (bool $paidOnly) => (float) Invoice::forUser($user)
            ->where('has_due', true)
            ->when($paidOnly, fn ($q) => $q->where('due_paid', true))
            ->selectRaw('COALESCE(SUM(ROUND(amount * ?, 2)), 0) as s', [$rate])
            ->value('s');

        $totalDue = $dueSum(false);
        $received = $dueSum(true);

        return view('dashboard', [
            'users'    => $user->isAdmin() ? User::count() : null,
            'projects' => Project::forUser($user)->count(),
            'openMonths' => Month::forUser($user)->where('closed', false)->count(),
            'invoices' => Invoice::forUser($user)->count(),
            'totalDue' => $totalDue,
            'received' => $received,
            'pending'  => $totalDue - $received,
            'upcoming' => Invoice::forUser($user)
                ->with('month.project')
                ->where('has_due', true)->where('due_paid', false)
                ->orderBy('due_date')->limit(10)->get(),
        ]);
    }
}
