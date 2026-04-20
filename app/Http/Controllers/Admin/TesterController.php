<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TesterController extends Controller
{
    public function index()
    {
        $testers = \App\Models\User::where('role', 'Tester')->get();
        return view('testers.index', compact('testers'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'Tester',
            'email_verified_at' => now(), // Bypass verification
        ]);

        return redirect()->route('testers.index')->with('success', 'Tester created successfully.');
    }
}
