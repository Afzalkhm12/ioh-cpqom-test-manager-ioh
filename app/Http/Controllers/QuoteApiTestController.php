<?php

namespace App\Http\Controllers;

use App\Models\SalesforceUser;
use App\Models\TestModule;
use App\Services\CpqApiTestService;
use Illuminate\Http\Request;

class QuoteApiTestController extends Controller
{
    public function show(TestModule $testModule)
    {
        $sfUsers = SalesforceUser::orderBy('label')->get();
        return view('test-suite.api-test', compact('testModule', 'sfUsers'));
    }

    public function run(Request $request, TestModule $testModule, CpqApiTestService $service)
    {
        $request->validate([
            'opportunity_id'       => 'required|string',
            'quote_name'           => 'required|string|max:255',
            'price_list_id'        => 'required|string',
            'currency'             => 'required|string|max:10',
            'record_type_id'       => 'required|string',
            'product_count'        => 'required|integer|min:1|max:20',
            'randomize_attributes' => 'nullable|boolean',
            'override_pricing'     => 'nullable|boolean',
            'otc_override'         => 'nullable|numeric|min:0',
            'rc_override'          => 'nullable|numeric|min:0',
            'persona_id'           => 'nullable|exists:salesforce_users,id',
        ]);

        $sfUser = $request->input('persona_id')
            ? SalesforceUser::find($request->input('persona_id'))
            : null;

        $result = $service->runQuoteTest($request->only([
            'opportunity_id', 'quote_name', 'price_list_id', 'currency',
            'record_type_id', 'product_count', 'randomize_attributes',
            'override_pricing', 'otc_override', 'rc_override', 'persona_id',
        ]), $sfUser);

        return response()->json($result);
    }
}
