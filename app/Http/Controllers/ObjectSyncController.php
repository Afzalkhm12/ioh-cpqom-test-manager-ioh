<?php

namespace App\Http\Controllers;

class ObjectSyncController extends Controller
{
    public function index()
    {
        $objects = \App\Models\SalesforceObject::withCount('fields')->get();
        return view('object-sync.index', compact('objects'));
    }

    public function show(\App\Models\SalesforceObject $objectSync)
    {
        $objectSync->load('fields');
        return view('object-sync.show', ['object' => $objectSync]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'api_name' => 'required|string|max:255',
        ]);

        $apiName = trim($request->api_name);

        try {
            $sfService = new \App\Services\SalesforceService();
            $token = $sfService->getAccessToken();
            if (! $token) {
                throw new \Exception('Could not obtain a Salesforce access token. Check SALESFORCE_CLIENT_ID and SALESFORCE_CLIENT_SECRET in your .env file.');
            }

            $describeData = $sfService->describeObject($apiName, false, $token);
            
            // Create or update Object
            $sfObj = \App\Models\SalesforceObject::updateOrCreate(
                ['api_name' => $describeData['name']],
                [
                    'label' => $describeData['label'],
                    'is_creatable' => $describeData['createable'],
                    'is_updatable' => $describeData['updateable'],
                    'is_deletable' => $describeData['deletable'],
                ]
            );

            // Dump old fields and recreate
            $sfObj->fields()->delete(); 

            $fieldsToInsert = [];
            foreach ($describeData['fields'] as $field) {
                // Determine readability checking standard filterable/custom flags or default to true
                $isReadable = isset($field['filterable']) ? $field['filterable'] : true;

                $picklistValues = null;
                if (in_array($field['type'], ['picklist', 'multipicklist']) && !empty($field['picklistValues'])) {
                    $picklistValues = json_encode(
                        array_values(array_map(
                            fn($v) => ['label' => $v['label'], 'value' => $v['value']],
                            array_filter($field['picklistValues'], fn($v) => $v['active'] ?? true)
                        ))
                    );
                }

                $fieldsToInsert[] = [
                    'salesforce_object_id' => $sfObj->id,
                    'label' => $field['label'],
                    'api_name' => $field['name'],
                    'type' => $field['type'],
                    'referenced_to' => $field['referenceTo'][0] ?? null,
                    'picklist_values' => $picklistValues,
                    'is_insertable' => $field['createable'] ?? false,
                    'is_updatable' => $field['updateable'] ?? false,
                    'is_readable' => $isReadable,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            // Chunk insert for performance on large objects like Account
            foreach(array_chunk($fieldsToInsert, 200) as $chunk) {
                \App\Models\SalesforceField::insert($chunk);
            }

            return redirect()->route('object-sync.show', $sfObj)->with('success', "{$sfObj->label} Object synced successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['api_name' => $e->getMessage()]);
        }
    }
    
    public function destroy(\App\Models\SalesforceObject $objectSync)
    {
        $objectSync->delete();
        return redirect()->route('object-sync.index')->with('success', 'Object removed from internal dictionary.');
    }
}
