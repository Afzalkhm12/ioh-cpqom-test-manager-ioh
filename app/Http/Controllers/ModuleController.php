<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = \App\Models\Module::withCount('testCases')->get();
        return view('modules.index', compact('modules'));
    }

    public function show(\App\Models\Module $module)
    {
        $module->load(['testModules.spec']);
        $testModules = \App\Models\TestModule::orderBy('display_name')->get();
        return view('modules.show', compact('module', 'testModules'));
    }

    public function linkTestSuite(Request $request, \App\Models\Module $module)
    {
        $request->validate([
            'test_module_ids'   => 'nullable|array',
            'test_module_ids.*' => 'exists:test_modules,id',
        ]);

        $module->testModules()->sync($request->input('test_module_ids', []));

        return redirect()->route('modules.show', $module)
            ->with('success', 'Test suites updated.');
    }
}
