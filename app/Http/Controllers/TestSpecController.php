<?php

namespace App\Http\Controllers;

use App\Models\TestSpec;
use Illuminate\Http\Request;

class TestSpecController extends Controller
{
    public function index()
    {
        $specs = TestSpec::withCount('testModules')->orderBy('display_name')->get();
        return view('test-specs.index', compact('specs'));
    }

    public function store(Request $request)
    {
        $isApi = $request->input('test_type') === 'api';

        $request->validate([
            'display_name' => 'required|string|max:255',
            'runner_key'   => $isApi ? 'nullable|string|max:100' : 'required|string|max:100|unique:test_specs,runner_key',
            'file_path'    => $isApi ? 'nullable|string|max:255' : 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'test_type'    => 'required|in:ui,api',
        ]);

        $runnerKey = $isApi
            ? ($request->input('runner_key') ?: 'api_' . \Str::slug($request->input('display_name')) . '_' . time())
            : $request->input('runner_key');

        TestSpec::create([
            'display_name' => $request->input('display_name'),
            'runner_key'   => $runnerKey,
            'file_path'    => $isApi ? null : $request->input('file_path'),
            'description'  => $request->input('description'),
            'test_type'    => $request->input('test_type'),
        ]);

        return back()->with('success', "Spec '{$request->display_name}' created.");
    }

    public function update(Request $request, TestSpec $testSpec)
    {
        $isApi = $request->input('test_type') === 'api';

        $request->validate([
            'display_name' => 'required|string|max:255',
            'runner_key'   => $isApi ? 'nullable|string|max:100' : 'required|string|max:100|unique:test_specs,runner_key,' . $testSpec->id,
            'file_path'    => $isApi ? 'nullable|string|max:255' : 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'test_type'    => 'required|in:ui,api',
        ]);

        $runnerKey = $isApi
            ? ($request->input('runner_key') ?: $testSpec->runner_key)
            : $request->input('runner_key');

        $testSpec->update([
            'display_name' => $request->input('display_name'),
            'runner_key'   => $runnerKey,
            'file_path'    => $isApi ? null : $request->input('file_path'),
            'description'  => $request->input('description'),
            'test_type'    => $request->input('test_type'),
        ]);

        return back()->with('success', "Spec '{$testSpec->display_name}' updated.");
    }

    public function destroy(TestSpec $testSpec)
    {
        $name = $testSpec->display_name;
        $testSpec->delete();
        return back()->with('success', "Spec '{$name}' deleted.");
    }
}
