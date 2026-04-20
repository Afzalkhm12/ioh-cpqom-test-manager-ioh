<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class AccountManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $module = Module::updateOrCreate([
            'name' => 'Account Management'
        ], [
            'description' => 'Manage Salesforce Accounts including creating, updating, and querying customer records.',
            'api_schema' => [
                'endpoint' => '/services/data/v60.0/sobjects/Account/',
                'method' => 'POST',
                'fields' => [
                    ['name' => 'Name', 'type' => 'text', 'required' => true],
                    ['name' => 'Industry', 'type' => 'text', 'required' => false],
                    ['name' => 'Phone', 'type' => 'text', 'required' => false]
                ]
            ]
        ]);

        $config = [
            'method' => 'POST',
            'endpoint' => '/services/data/v60.0/sobjects/Account/',
            'payload' => [
                'Name' => 'Test API Account',
                'Industry' => 'Technology',
                'Phone' => '+1234567890'
            ]
        ];

        $module->testCases()->firstOrCreate([
            'title' => 'Create Valid Account via API'
        ], [
            'type' => 'API',
            'configuration' => $config
        ]);
    }
}
