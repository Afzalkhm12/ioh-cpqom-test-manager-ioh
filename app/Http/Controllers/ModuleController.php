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
        $module->load(['testCases.testRuns' => function($query) {
            $query->latest()->take(5);
        }]);
        $sfUsers = \App\Models\SalesforceUser::all();
        return view('modules.show', compact('module', 'sfUsers'));
    }
}
