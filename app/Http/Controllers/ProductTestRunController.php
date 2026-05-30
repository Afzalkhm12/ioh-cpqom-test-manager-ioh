<?php

namespace App\Http\Controllers;

use App\Models\ProductTestRun;
use App\Models\ProductTestSuite;
use App\Models\TestModule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ProductTestRunController extends Controller
{
    public function show(ProductTestRun $productTestRun)
    {
        return response()->json($this->format($productTestRun));
    }

    public function history(ProductTestSuite $productTestSuite, TestModule $testModule)
    {
        $runs = ProductTestRun::where('product_test_suite_id', $productTestSuite->id)
            ->where('test_module_id', $testModule->id)
            ->where('user_id', auth()->id())
            ->orderByDesc('started_at')
            ->paginate(20);

        $jiraUrl = rtrim(env('JIRA_URL', ''), '/');

        return view('product-test-runs.history', compact('productTestSuite', 'testModule', 'runs', 'jiraUrl'));
    }

    public function update(Request $request, ProductTestRun $productTestRun)
    {
        $data = $request->validate([
            'status'            => 'nullable|in:running,success,error,aborted',
            'log'               => 'nullable|string',
            'jira_ticket'       => 'nullable|string|max:50',
            'created_ids'       => 'nullable|array',
            'validation_status' => 'nullable|in:passed,not_passed',
        ]);

        if (isset($data['status'])) {
            $productTestRun->status = $data['status'];
        }

        if (isset($data['log'])) {
            $productTestRun->log = $data['log'];
        }

        if (isset($data['jira_ticket'])) {
            $productTestRun->jira_ticket = $data['jira_ticket'];
        }

        if (array_key_exists('created_ids', $data)) {
            $existing = $productTestRun->created_ids ?? [];
            $productTestRun->created_ids = array_merge($existing, $data['created_ids']);
        }

        if (isset($data['validation_status'])) {
            $productTestRun->validation_status = $data['validation_status'];
        }

        if ($productTestRun->isTerminal() && ! $productTestRun->finished_at) {
            $productTestRun->finished_at = Carbon::now();
        }

        $productTestRun->save();

        return response()->json($this->format($productTestRun));
    }

    public function storeFinding(Request $request, ProductTestRun $productTestRun)
    {
        $data = $request->validate([
            'finding'    => 'required|string',
            'images'     => 'nullable|array',
            'images.*'   => 'image|max:10240',
        ]);

        $paths = $productTestRun->evidence_images ?? [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store("evidence/{$productTestRun->id}", 'public');
            }
        }

        $productTestRun->update([
            'validation_status' => 'not_passed',
            'finding'           => $data['finding'],
            'evidence_images'   => $paths,
        ]);

        return response()->json([
            'validation_status' => $productTestRun->validation_status,
            'finding'           => $productTestRun->finding,
            'evidence_images'   => array_map(
                fn($p) => Storage::url($p),
                $productTestRun->evidence_images ?? []
            ),
        ]);
    }

    private function format(ProductTestRun $run): array
    {
        return [
            'id'                => $run->id,
            'status'            => $run->status,
            'log'               => $run->log,
            'jira_ticket'       => $run->jira_ticket,
            'created_ids'       => $run->created_ids,
            'validation_status' => $run->validation_status,
            'finding'           => $run->finding,
            'evidence_images'   => array_map(
                fn($p) => Storage::url($p),
                $run->evidence_images ?? []
            ),
            'started_at'        => $run->started_at?->toISOString(),
            'finished_at'       => $run->finished_at?->toISOString(),
        ];
    }
}
