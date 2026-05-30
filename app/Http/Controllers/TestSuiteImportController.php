<?php

namespace App\Http\Controllers;

use App\Models\TestModule;
use App\Models\TestParameter;
use App\Models\TestSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestSuiteImportController extends Controller
{
    public function index()
    {
        $specs = TestSpec::orderBy('display_name')->get();
        return view('test-suite.import', compact('specs'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file'     => ['required', 'file', 'mimes:xlsx,xls,ods', 'max:10240'],
            'category' => ['required', 'string', 'max:100'],
            'spec_id'  => ['nullable', 'integer', 'exists:test_specs,id'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $parsed = $this->parseSpreadsheet($spreadsheet, trim($request->string('category')));

        if (empty($parsed)) {
            return back()->withErrors(['file' => 'No valid test data found in the file. Check that the header row contains "Step Details".']);
        }

        // Mark which modules / test-cases already exist in DB
        $existingModules = TestModule::whereIn('module_key', array_keys($parsed))
            ->pluck('id', 'module_key')
            ->all();

        foreach ($parsed as $moduleKey => &$moduleData) {
            $existingModuleId = $existingModules[$moduleKey] ?? null;
            $moduleData['exists'] = $existingModuleId !== null;

            $existingTcIds = $existingModuleId
                ? TestParameter::where('module_id', $existingModuleId)->where('user_id', auth()->id())->pluck('test_case_id', 'test_case_id')->all()
                : [];

            foreach ($moduleData['test_cases'] as &$tcData) {
                $tcData['exists'] = isset($existingTcIds[$tcData['test_case_id']]);
            }
            unset($tcData);
        }
        unset($moduleData);

        $request->session()->put('excel_import', [
            'category' => trim($request->string('category')),
            'spec_id'  => $request->integer('spec_id') ?: null,
            'modules'  => $parsed,
        ]);

        $specs = TestSpec::orderBy('display_name')->get();
        return view('test-suite.import', compact('specs', 'parsed'));
    }

    public function confirm(Request $request)
    {
        $payload = $request->session()->pull('excel_import');

        if (!$payload) {
            return redirect()->route('test-suite.import.index')
                ->withErrors(['error' => 'Import session expired. Please upload the file again.']);
        }

        $specId   = $payload['spec_id'];
        $category = $payload['category'] ?? null;
        $modulesCreated = $modulesUpdated = $tcCreated = $tcUpdated = 0;

        foreach ($payload['modules'] as $moduleKey => $moduleData) {
            $values = [
                'display_name'     => $moduleData['display_name'],
                'description'      => $moduleData['description'],
                'salesforce_module' => $moduleData['salesforce_module'],
                'category'         => $category,
            ];
            if (!$moduleData['exists'] && $specId) {
                $values['spec_id'] = $specId;
            }

            $module = TestModule::updateOrCreate(['module_key' => $moduleKey], $values);
            $moduleData['exists'] ? $modulesUpdated++ : $modulesCreated++;

            foreach ($moduleData['test_cases'] as $tcData) {
                TestParameter::updateOrCreate(
                    ['module_id' => $module->id, 'test_case_id' => $tcData['test_case_id'], 'user_id' => auth()->id()],
                    ['parameters' => $tcData['parameters'], 'notes' => $tcData['notes']]
                );
                $tcData['exists'] ? $tcUpdated++ : $tcCreated++;
            }
        }

        return redirect()->route('test-suite.index')->with('success',
            "Import complete: {$modulesCreated} modules created, {$modulesUpdated} updated; {$tcCreated} test cases created, {$tcUpdated} updated."
        );
    }

    private function parseSpreadsheet($spreadsheet, string $category = ''): array
    {
        $result = [];

        $headerAliases = [
            'testing_stream'    => ['testing stream', 'stream'],
            'persona'           => ['persona'],
            'topic'             => ['topic'],
            'scenario'          => ['scenario'],
            'test_case_no'      => ['test case no', 'test case no.', 'test case number', 'tc no', 'tc #', 'test case #'],
            'integrated_system' => ['integrated system'],
            'required_data'     => ['required data from ioh', 'required data'],
            'pre_requisite'     => ['pre-requisite', 'pre requisite', 'prerequisite'],
            'step_no'           => ['step #', 'step no', 'step number', 'step'],
            'step_details'      => ['step details', 'steps'],
            'expected_results'  => ['expected results', 'expected result'],
        ];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $sheetName = $sheet->getTitle();
            $rows = $sheet->toArray(null, false, false, false);

            if (count($rows) < 2) {
                continue;
            }

            // Detect header row by scanning for known column labels
            $headerIndex = null;
            $colMap = array_fill_keys(array_keys($headerAliases), null);

            foreach ($rows as $i => $row) {
                $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string) $v) !== '');
                if (count($nonEmpty) < 3) {
                    continue;
                }

                $candidate = array_fill_keys(array_keys($headerAliases), null);
                foreach ($row as $colIdx => $cell) {
                    $norm = strtolower(trim((string) $cell));
                    foreach ($headerAliases as $field => $aliases) {
                        if (in_array($norm, $aliases, true) && $candidate[$field] === null) {
                            $candidate[$field] = $colIdx;
                        }
                    }
                }

                if ($candidate['step_details'] !== null) {
                    $headerIndex = $i;
                    $colMap = $candidate;
                    break;
                }
            }

            if ($headerIndex === null) {
                continue;
            }

            $carry = array_fill_keys(array_keys($colMap), null);

            foreach ($rows as $rowIdx => $row) {
                if ($rowIdx <= $headerIndex) {
                    continue;
                }

                // Reset carry when testing_stream column has a new value (new section begins)
                if ($colMap['testing_stream'] !== null) {
                    $streamRaw = trim((string) ($row[$colMap['testing_stream']] ?? ''));
                    if ($streamRaw !== '') {
                        $carry = array_fill_keys(array_keys($colMap), null);
                    }
                }

                // Carry-forward non-null values for merged-cell continuations
                $eff = [];
                foreach ($colMap as $field => $colIdx) {
                    if ($colIdx === null) {
                        $eff[$field] = null;
                        continue;
                    }
                    $raw = $row[$colIdx] ?? null;
                    $val = $raw !== null ? trim((string) $raw) : '';
                    $val = $val === '' ? null : $val;
                    $carry[$field] = $val ?? $carry[$field];
                    $eff[$field] = $carry[$field];
                }

                if (empty($eff['step_details']) || empty($eff['topic'])) {
                    continue;
                }

                $stream    = $eff['testing_stream'] ?? 'General';
                $topic     = $eff['topic'];
                $moduleKey = Str::substr(
                    Str::slug($category) . '_' . Str::slug($stream) . '_' . Str::slug($topic),
                    0, 100
                );
                $tcId      = Str::substr(trim((string) ($eff['test_case_no'] ?? '')), 0, 20);

                if ($tcId === '') {
                    continue;
                }

                if (!isset($result[$moduleKey])) {
                    $result[$moduleKey] = [
                        'module_key'        => $moduleKey,
                        'display_name'      => $topic,
                        'salesforce_module' => $stream,
                        'description'       => $eff['scenario'], // first scenario encountered
                        'sheet'             => $sheetName,
                        'exists'            => false,
                        'test_cases'        => [],
                    ];
                }

                if (!isset($result[$moduleKey]['test_cases'][$tcId])) {
                    $result[$moduleKey]['test_cases'][$tcId] = [
                        'test_case_id' => $tcId,
                        'notes'        => $eff['scenario'],
                        'exists'       => false,
                        'parameters'   => [
                            'salesforce_module' => $stream,
                            'persona'           => $eff['persona'],
                            'scenario'          => $eff['scenario'],
                            'pre_requisite'     => $eff['pre_requisite'],
                            'integrated_system' => $eff['integrated_system'],
                            'required_data'     => $eff['required_data'],
                            'steps'             => [],
                        ],
                    ];
                }

                $result[$moduleKey]['test_cases'][$tcId]['parameters']['steps'][] = [
                    'step'     => (int) ($eff['step_no'] ?? 0),
                    'details'  => $eff['step_details'],
                    'expected' => $eff['expected_results'],
                ];
            }
        }

        return $result;
    }
}
