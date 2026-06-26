<?php

namespace App\Http\Controllers;

use App\Models\RuntimeState;
use App\Models\SalesforceObject;
use App\Models\TestSpec;
use App\Services\SalesforceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiBuilderController extends Controller
{
    public function show(TestSpec $testSpec)
    {
        abort_if($testSpec->test_type !== 'api', 404);

        $objects     = SalesforceObject::with('fields')->orderBy('label')->get();
        $runtimeKeys = RuntimeState::where('user_id', auth()->id())->pluck('state_key')->sort()->values();

        return view('api-builder.show', compact('testSpec', 'objects', 'runtimeKeys'));
    }

    public function saveConfig(Request $request, TestSpec $testSpec)
    {
        abort_if($testSpec->test_type !== 'api', 404);

        $request->validate([
            'config' => 'required|array',
        ]);

        $testSpec->update(['api_config' => $request->input('config')]);

        return response()->json(['success' => true]);
    }

    public function execute(Request $request, TestSpec $testSpec)
    {
        abort_if($testSpec->test_type !== 'api', 404);

        $config = $testSpec->api_config;
        if (empty($config['steps'])) {
            return response()->json(['success' => false, 'error' => 'No steps configured.'], 422);
        }

        $sf    = new SalesforceService();
        $token = $sf->getAccessToken();

        // Build runtime state lookup for {{key}} template resolution
        $runtimeMap = RuntimeState::where('user_id', auth()->id())
            ->pluck('state_value', 'state_key')
            ->all();

        $steps = $config['steps'];

        // Pre-resolve templates and name lookups
        foreach ($steps as &$step) {
            if (($step['operation'] ?? '') === 'query') {
                $step['where_clause'] = $this->resolveTemplates($step['where_clause'] ?? '', $runtimeMap);
            } else {
                foreach ($step['fields'] as &$field) {
                    $field['value'] = $this->resolveTemplates($field['value'] ?? '', $runtimeMap);

                    if (($field['type'] ?? '') === 'reference' && ($field['lookup_mode'] ?? 'id') === 'name') {
                        $id = $sf->lookupByName($field['referenced_to'], $field['value'], $token);
                        $field['value'] = $id ?? $field['value'];
                    }
                }
                unset($field);
            }
        }
        unset($step);

        if (count($steps) === 1) {
            return $this->executeSingle($sf, $token, $steps[0]);
        }

        return $this->executeComposite($sf, $token, $steps);
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'object' => 'required|string|max:100',
            'name'   => 'required|string|max:255',
        ]);

        $sf    = new SalesforceService();
        $token = $sf->getAccessToken();

        $id = $sf->lookupByName($request->object, $request->name, $token);

        if ($id) {
            return response()->json(['id' => $id, 'name' => $request->name]);
        }

        return response()->json(['error' => "No {$request->object} found with Name \"{$request->name}\""], 404);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function buildSOQL(array $step): string
    {
        $fields = !empty($step['select_fields']) ? $step['select_fields'] : 'Id, Name';
        $where  = !empty($step['where_clause']) ? ' WHERE ' . $step['where_clause'] : '';
        $limit  = ' LIMIT ' . (int)($step['query_limit'] ?? 10);
        return "SELECT {$fields} FROM {$step['object']}{$where}{$limit}";
    }

    private function resolveTemplates(string $value, array $runtimeMap): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function ($m) use ($runtimeMap) {
            return $runtimeMap[$m[1]] ?? $m[0];
        }, $value);
    }

    private function buildPayload(array $step): array
    {
        $payload = [];
        foreach ($step['fields'] as $field) {
            $payload[$field['api_name']] = $field['value'] ?? '';
        }
        return $payload;
    }

    private function executeSingle(SalesforceService $sf, string $token, array $step): \Illuminate\Http\JsonResponse
    {
        $object    = $step['object'];
        $operation = $step['operation'] ?? 'create';

        if ($operation === 'query') {
            $soql    = $this->buildSOQL($step);
            $result  = $sf->executeQuery($soql, $token);
            $records = $result['response']['records'] ?? [];
            return response()->json([
                'success' => $result['success'],
                'mode'    => 'rest',
                'steps'   => [[
                    'reference_id' => $step['reference_id'] ?? 'step_0',
                    'object'       => $object,
                    'operation'    => 'query',
                    'status'       => $result['status'],
                    'records'      => $records,
                    'total_size'   => $result['response']['totalSize'] ?? 0,
                    'success'      => $result['success'],
                    'body'         => $result['response'],
                ]],
            ]);
        }

        $payload = $this->buildPayload($step);

        if ($operation === 'update') {
            $recordId = $step['record_id'] ?? '';
            $result   = $sf->updateRecord($object, $recordId, $payload, $token);
        } else {
            $result = $sf->createRecord($object, $payload, $token);
        }

        $id = $result['response']['id'] ?? null;

        return response()->json([
            'success' => $result['success'],
            'mode'    => 'rest',
            'steps'   => [[
                'reference_id' => $step['reference_id'] ?? 'step_0',
                'object'       => $object,
                'operation'    => $operation,
                'status'       => $result['status'],
                'id'           => $id,
                'success'      => $result['success'],
                'body'         => $result['response'],
            ]],
        ]);
    }

    private function executeComposite(SalesforceService $sf, string $token, array $steps): \Illuminate\Http\JsonResponse
    {
        $compositeRequest = [];

        foreach ($steps as $index => $step) {
            $object      = $step['object'];
            $operation   = $step['operation'] ?? 'create';
            $referenceId = $step['reference_id'] ?? "step_{$index}";
            $payload     = $this->buildPayload($step);

            if ($operation === 'query') {
                $soql = $this->buildSOQL($step);
                $compositeRequest[] = [
                    'method'      => 'GET',
                    'url'         => "/services/data/v60.0/query?q=" . urlencode($soql),
                    'referenceId' => $referenceId,
                ];
            } elseif ($operation === 'update') {
                $recordId = $step['record_id'] ?? '';
                $compositeRequest[] = [
                    'method'      => 'PATCH',
                    'url'         => "/services/data/v60.0/sobjects/{$object}/{$recordId}",
                    'referenceId' => $referenceId,
                    'body'        => $payload,
                ];
            } else {
                $compositeRequest[] = [
                    'method'      => 'POST',
                    'url'         => "/services/data/v60.0/sobjects/{$object}",
                    'referenceId' => $referenceId,
                    'body'        => $payload,
                ];
            }
        }

        Log::debug('ApiBuilder composite request', ['subrequests' => $compositeRequest]);

        $result = $sf->executeComposite($compositeRequest, $token);

        Log::debug('ApiBuilder composite response', [
            'status'   => $result['status'],
            'success'  => $result['success'],
            'response' => $result['response'],
        ]);

        $stepResults = [];
        $subResults  = $result['response']['compositeResponse'] ?? [];

        foreach ($steps as $index => $step) {
            $sub        = $subResults[$index] ?? [];
            $body       = $sub['body'] ?? null;
            $httpStatus = $sub['httpStatusCode'] ?? null;
            $isSuccess  = $httpStatus && $httpStatus < 300;
            $isQuery    = ($step['operation'] ?? '') === 'query';
            $id         = !$isQuery && is_array($body) ? ($body['id'] ?? null) : null;
            $records    = $isQuery && is_array($body) ? ($body['records'] ?? []) : [];

            $stepResults[] = [
                'reference_id' => $step['reference_id'] ?? "step_{$index}",
                'object'       => $step['object'],
                'operation'    => $step['operation'] ?? 'create',
                'status'       => $httpStatus,
                'id'           => $id,
                'records'      => $records,
                'total_size'   => $isQuery && is_array($body) ? ($body['totalSize'] ?? 0) : null,
                'success'      => $isSuccess,
                'body'         => $body,
            ];
        }

        return response()->json([
            'success' => $result['success'],
            'mode'    => 'composite',
            'steps'   => $stepResults,
        ]);
    }
}
