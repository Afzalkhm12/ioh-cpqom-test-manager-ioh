<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestCaseController extends Controller
{
    public function store(\Illuminate\Http\Request $request, $moduleId)
    {
        $module = \App\Models\Module::findOrFail($moduleId);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:API,Apex,UI',
            'configuration' => 'required|string',
        ]);

        $type = $request->type;
        $configuration = [];

        if ($type === 'API' && $module->api_schema) {
            // We use the predefined schema endpoint and method, and the user provided payload
            $configuration = [
                'method' => $module->api_schema['method'],
                'endpoint' => $module->api_schema['endpoint'],
                'payload' => $request->payload ?? []
            ];
        } else {
            $configData = json_decode($request->configuration, true);
            if (json_last_error() !== JSON_ERROR_NONE && $type !== 'UI') {
                $configData = ['path' => $request->configuration];
            }
            $configuration = $configData ?? ['path' => $request->configuration];
        }

        $module->testCases()->create([
            'title' => $request->title,
            'type' => $type,
            'configuration' => $configuration,
        ]);

        return redirect()->route('modules.show', $module)->with('success', 'Test case created successfully.');
    }
}
