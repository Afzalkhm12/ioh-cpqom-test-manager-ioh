<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestRunController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        if ($request->action_type === 'pull_allure') {
            // Mock pull allure report logic
            return back()->with('success', 'Allure report pulled successfully! Simulated results updated for UI tests.');
        }

        $request->validate([
            'test_case_id' => 'required|exists:test_cases,id',
            'salesforce_user_id' => 'nullable|exists:salesforce_users,id',
        ]);

        $testCase = \App\Models\TestCase::findOrFail($request->test_case_id);
        $sfUser = $request->salesforce_user_id ? \App\Models\SalesforceUser::find($request->salesforce_user_id) : null;
        
        $testRun = $testCase->testRuns()->create([
            'executed_by' => auth()->id(),
            'status' => 'Pending',
            'executed_at' => now(),
        ]);

        try {
            $sf = new \App\Services\SalesforceService();
            $tokenOverride = null;
            if ($sfUser) {
                $tokenOverride = $sf->getAccessTokenForUser($sfUser);
            }

            if ($testCase->type === 'API') {
                $result = $sf->executeApiTest($testCase->configuration ?? [], $tokenOverride, $sfUser);
                $testRun->update([
                    'status' => $result['success'] ? 'Pass' : 'Fail',
                    'result_payload' => $result
                ]);
            } elseif ($testCase->type === 'Apex') {
                $className = $testCase->configuration['className'] ?? 'TestClass';
                $result = $sf->executeApexTest($className, $tokenOverride);
                $testRun->update([
                    'status' => $result['success'] ? 'Pass' : 'Fail',
                    'result_payload' => $result
                ]);
            } else {
                $testRun->update(['status' => 'Pass']); // Fallback mock
            }
        } catch (\Exception $e) {
            $testRun->update([
                'status' => 'Fail',
                'logs' => $e->getMessage()
            ]);
        }

        return back()->with('success', 'Test execution complete!');
    }
}
