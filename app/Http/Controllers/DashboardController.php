<?php

namespace App\Http\Controllers;

use App\Models\ProductTestRun;
use App\Models\ProductTestSuite;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // One latest run per (suite, module) pair
        $latestRunIds = DB::table('product_test_runs')
            ->selectRaw('MAX(id) as id')
            ->groupBy('product_test_suite_id', 'test_module_id');

        $allLatestRuns = ProductTestRun::whereIn('id', $latestRunIds)->get();

        // Global headline stats
        $totalSuites     = ProductTestSuite::count();
        $globalPassed    = $allLatestRuns->where('validation_status', 'passed')->count();
        $globalNotPassed = $allLatestRuns->where('validation_status', 'not_passed')->count();

        // Per-suite stats
        $latestRunsBySuite = $allLatestRuns->groupBy('product_test_suite_id');

        $suites = ProductTestSuite::with('product')
            ->withCount('modules')
            ->get()
            ->sortBy([['product.product_line', 'asc'], ['product.product_offer', 'asc']])
            ->map(function ($suite) use ($latestRunsBySuite) {
                $runs = $latestRunsBySuite->get($suite->id, collect());
                $suite->stat_passed     = $runs->where('validation_status', 'passed')->count();
                $suite->stat_not_passed = $runs->where('validation_status', 'not_passed')->count();
                $suite->stat_error      = $runs->filter(
                    fn($r) => in_array($r->status, ['error', 'aborted']) && $r->validation_status === null
                )->count();
                $suite->stat_not_run    = $suite->modules_count - $runs->count();
                $suite->last_run_at     = $runs->sortByDesc('started_at')->first()?->started_at;
                return $suite;
            });

        $suitesByLine = $suites->groupBy('product.product_line');

        // Suites where every module is validated as passed
        $fullyPassedSuites = $suites->filter(
            fn($s) => $s->modules_count > 0 && $s->stat_passed === $s->modules_count
        )->count();

        // Recent run activity
        $recentRuns = ProductTestRun::with(['suite.product', 'module'])
            ->orderByDesc('started_at')
            ->limit(12)
            ->get();

        return view('dashboard', compact(
            'totalSuites', 'globalPassed', 'globalNotPassed', 'fullyPassedSuites',
            'suitesByLine', 'recentRuns'
        ));
    }
}
