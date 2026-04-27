<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSuiteSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Test Modules ────────────────────────────────────────────────
        $modules = [
            ['module_key' => 'account_mgmt', 'display_name' => 'Account Management',    'counter' => 22, 'description' => 'Non-IDA account creation and management flows'],
            ['module_key' => 'lead_mgmt',    'display_name' => 'Lead Management',        'counter' => 29, 'description' => 'Lead creation, conversion, and list view tests'],
            ['module_key' => 'oppty_mgmt',   'display_name' => 'Opportunity Management', 'counter' => 12, 'description' => 'Opportunity creation, product addition, and pricing tests'],
            ['module_key' => 'quote_mgmt',   'display_name' => 'Quote Management',       'counter' => 12, 'description' => 'Quote creation and CPQ configuration tests'],
        ];

        foreach ($modules as $module) {
            DB::table('test_modules')->updateOrInsert(
                ['module_key' => $module['module_key']],
                array_merge($module, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // ── 2. Test Parameters ─────────────────────────────────────────────
        $moduleIds = DB::table('test_modules')->pluck('id', 'module_key');

        $parameters = [

            // Account Management
            [
                'module_key'   => 'account_mgmt',
                'test_case_id' => 'tc001',
                'parameters'   => ['accountName' => 'Test CCA', 'phone' => '25567889'],
            ],
            [
                'module_key'   => 'account_mgmt',
                'test_case_id' => 'tc002',
                'parameters'   => [
                    'accountName'   => 'Test CA',
                    'idReference'   => '123456789012',
                    'phone'         => '25567889',
                    'accountOption' => 'Test CA',
                ],
            ],

            // Lead Management
            [
                'module_key'   => 'lead_mgmt',
                'test_case_id' => 'tc001',
                'parameters'   => [
                    'listViewName'    => 'All my Lead',
                    'expectedColumns' => ['Project Name', 'Created By Alias'],
                ],
            ],
            [
                'module_key'   => 'lead_mgmt',
                'test_case_id' => 'tc002',
                'parameters'   => [
                    'accountName'          => 'TEST CA 22',
                    'accountOption'        => 'TEST CA 22',
                    'rfsDateMonthsAhead'   => 2,
                    'rfsDateDay'           => '30',
                    'projectName'          => 'Mantap bos',
                    'company'              => 'Pertamax Bos',
                    'leadSource'           => 'Indosat Vendor Data',
                    'description'          => 'Created by Automation Testing',
                    'leadCurrency'         => 'IDR - Indonesian Rupiah',
                    'primaryContactSearch' => 'test contact',
                    'primaryContactOption' => 'Test Contact TEST CREATE CA',
                    'lastName'             => 'Agus',
                    'mobile'               => '0817456789',
                    'typeOfProduct'        => 'Connectivity',
                    'function'             => 'IT',
                    'budgetStatus'         => 'Budget available',
                    'roleOfLeadSeniority'  => 'Enterprise (Director / Vice',
                    'timeframe'            => '-3 Months',
                    'newRequirements'      => 'Yes',
                    'existingCustomer'     => 'Yes',
                    'leadType'             => 'Customer/End User',
                    'expectedLeadOwner'    => 'OCKY HARLIANSYAH',
                    'expectedLeadStatus'   => 'New',
                ],
            ],
            [
                'module_key'   => 'lead_mgmt',
                'test_case_id' => 'tc008',
                'parameters'   => [], // reserved
            ],

            // Opportunity Management
            [
                'module_key'   => 'oppty_mgmt',
                'test_case_id' => 'tc002',
                'parameters'   => [
                    'accountName' => 'Test CA',
                    'idReference' => '123456789012',
                    'phone'       => '25567889',
                ],
            ],
            [
                'module_key'   => 'oppty_mgmt',
                'test_case_id' => 'tc010',
                'parameters'   => [
                    'productName'           => 'Alibaba Cloud IDR',
                    'otc'                   => '5000000',
                    'mrc'                   => '10000000',
                    'expectedOtc'           => 'IDR 5,000,000',
                    'expectedMrc'           => 'IDR 10,000,000',
                    'expectedTotal'         => 'IDR 245,000,000',
                    'expectedAnnualRevenue' => 'IDR 122,500,000',
                ],
            ],

            // Quote Management
            [
                'module_key'   => 'quote_mgmt',
                'test_case_id' => 'tc002',
                'parameters'   => [
                    'accountName' => 'Test CA',
                    'idReference' => '123456789012',
                    'phone'       => '25567889',
                ],
            ],
            [
                'module_key'   => 'quote_mgmt',
                'test_case_id' => 'tc010',
                'parameters'   => [
                    'productName'           => 'Alibaba Cloud IDR',
                    'otc'                   => '5000000',
                    'mrc'                   => '10000000',
                    'expectedOtc'           => 'IDR 5,000,000',
                    'expectedMrc'           => 'IDR 10,000,000',
                    'expectedTotal'         => 'IDR 245,000,000',
                    'expectedAnnualRevenue' => 'IDR 122,500,000',
                ],
            ],
        ];

        foreach ($parameters as $row) {
            DB::table('test_parameters')->updateOrInsert(
                [
                    'module_id'    => $moduleIds[$row['module_key']],
                    'test_case_id' => $row['test_case_id'],
                ],
                [
                    'parameters' => json_encode($row['parameters']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // ── 3. Runtime State ──────────────────────────────────────────────
        $runtimeState = [
            [
                'state_key'   => 'opportunityId',
                'state_value' => '006MS000008yNRqYAM',
                'description' => 'Salesforce Opportunity ID — set after lead-conversion test; consumed by oppty_mgmt and quote_mgmt specs',
            ],
        ];

        foreach ($runtimeState as $state) {
            DB::table('runtime_state')->updateOrInsert(
                ['state_key' => $state['state_key']],
                array_merge($state, ['last_updated_at' => now()])
            );
        }

        $this->command->info('✓ TestSuiteSeeder: seeded test_modules, test_parameters, runtime_state.');
    }
}
