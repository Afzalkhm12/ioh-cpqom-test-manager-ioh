<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesforceUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sfUsers = \App\Models\SalesforceUser::all();
        return view('sf-users.index', compact('sfUsers'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'username' => 'required|string|email|max:255|unique:salesforce_users',
        ]);

        \App\Models\SalesforceUser::create($request->only('label', 'username'));

        return redirect()->route('sf-users.index')->with('success', 'Salesforce Persona created successfully. Please authorize it now.');
    }

    public function destroy($id)
    {
        $sfUser = \App\Models\SalesforceUser::findOrFail($id);
        $sfUser->delete();
        return redirect()->route('sf-users.index')->with('success', 'Persona deleted.');
    }
}
