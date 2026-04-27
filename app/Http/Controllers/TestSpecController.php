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
        $request->validate([
            'display_name' => 'required|string|max:255',
            'runner_key'   => 'required|string|max:100|unique:test_specs,runner_key',
            'file_path'    => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
        ]);

        TestSpec::create($request->only(['display_name', 'runner_key', 'file_path', 'description']));

        return back()->with('success', "Spec '{$request->display_name}' created.");
    }

    public function update(Request $request, TestSpec $testSpec)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'runner_key'   => 'required|string|max:100|unique:test_specs,runner_key,' . $testSpec->id,
            'file_path'    => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
        ]);

        $testSpec->update($request->only(['display_name', 'runner_key', 'file_path', 'description']));

        return back()->with('success', "Spec '{$testSpec->display_name}' updated.");
    }

    public function destroy(TestSpec $testSpec)
    {
        $name = $testSpec->display_name;
        $testSpec->delete();
        return back()->with('success', "Spec '{$name}' deleted.");
    }
}
