<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-brand-dark leading-tight">
                Import Test Scenarios from Excel
            </h2>
            <a href="{{ route('test-suite.index') }}" class="text-sm text-brand-teal hover:underline font-medium">
                ← Back to Test Suite
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!isset($parsed))
            {{-- ── Upload Form ───────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-bold text-brand-dark mb-1">Upload Excel File</h3>
                <p class="text-sm text-gray-500 mb-6">
                    The file must have a header row with columns: <span class="font-mono text-xs bg-gray-100 px-1 rounded">Testing Stream</span>,
                    <span class="font-mono text-xs bg-gray-100 px-1 rounded">Topic</span>,
                    <span class="font-mono text-xs bg-gray-100 px-1 rounded">Test Case No</span>,
                    <span class="font-mono text-xs bg-gray-100 px-1 rounded">Step #</span>,
                    <span class="font-mono text-xs bg-gray-100 px-1 rounded">Step Details</span>,
                    <span class="font-mono text-xs bg-gray-100 px-1 rounded">Expected Results</span>.
                    Multiple sheets are supported.
                </p>

                <form method="POST" action="{{ route('test-suite.import.preview') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="category" value="Category Name" />
                        <x-text-input id="category" name="category" type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g. Lead Management, Account Management, CPQ Flow…"
                            value="{{ old('category') }}"
                            required />
                        <p class="mt-1 text-xs text-gray-400">All modules from this file will be grouped under this category.</p>
                    </div>

                    <div>
                        <x-input-label for="file" value="Excel File (.xlsx, .xls, .ods)" />
                        <input id="file" name="file" type="file"
                            accept=".xlsx,.xls,.ods"
                            class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-brand-teal p-2"
                            required />
                        <p class="mt-1 text-xs text-gray-400">Max 10 MB</p>
                    </div>

                    @if($specs->count() > 0)
                    <div>
                        <x-input-label for="spec_id" value="Default Spec (optional)" />
                        <select id="spec_id" name="spec_id"
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-teal focus:ring-brand-teal text-sm">
                            <option value="">— No default spec —</option>
                            @foreach($specs as $spec)
                                <option value="{{ $spec->id }}">{{ $spec->display_name }} ({{ $spec->test_type }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Only assigned to newly created modules.</p>
                    </div>
                    @endif

                    <div class="pt-2">
                        <button type="submit"
                            class="px-6 py-2.5 bg-brand-teal text-white rounded-lg text-sm font-semibold hover:opacity-90 transition">
                            Preview Import →
                        </button>
                    </div>
                </form>
            </div>

            {{-- Column format reference --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-brand-dark mb-3">Supported Column Format</h3>
                <div class="overflow-x-auto">
                    <table class="text-xs text-gray-600 w-full">
                        <thead class="bg-gray-50 text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">Excel Column</th>
                                <th class="px-3 py-2 text-left">Stored As</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr><td class="px-3 py-2 font-mono">Testing Stream</td><td class="px-3 py-2">Part of module_key &amp; description</td><td class="px-3 py-2 text-gray-400">Combined with Topic</td></tr>
                            <tr><td class="px-3 py-2 font-mono">Topic</td><td class="px-3 py-2">TestModule.display_name</td><td class="px-3 py-2 text-gray-400">One TestModule per unique Topic</td></tr>
                            <tr><td class="px-3 py-2 font-mono">Test Case No</td><td class="px-3 py-2">TestParameter.test_case_id</td><td class="px-3 py-2 text-gray-400">Max 20 characters</td></tr>
                            <tr><td class="px-3 py-2 font-mono">Scenario</td><td class="px-3 py-2">TestParameter.notes</td><td class="px-3 py-2 text-gray-400">—</td></tr>
                            <tr><td class="px-3 py-2 font-mono">Persona, Pre-requisite, etc.</td><td class="px-3 py-2">parameters.persona, parameters.pre_requisite</td><td class="px-3 py-2 text-gray-400">Inside JSONB parameters</td></tr>
                            <tr><td class="px-3 py-2 font-mono">Step #, Step Details, Expected Results</td><td class="px-3 py-2">parameters.steps[ ]</td><td class="px-3 py-2 text-gray-400">Array of steps</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @else
            {{-- ── Preview ───────────────────────────────────────────────── --}}
            @php
                $importCategory = session('excel_import.category', '—');
            @endphp
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 text-sm text-blue-800">
                Category: <strong>{{ $importCategory }}</strong> — all modules below will be tagged with this label.
            </div>
            @php
                $totalTc = collect($parsed)->sum(fn($m) => count($m['test_cases']));
                $newModules = collect($parsed)->filter(fn($m) => !$m['exists'])->count();
                $updateModules = collect($parsed)->filter(fn($m) => $m['exists'])->count();
                $newTc = collect($parsed)->sum(fn($m) => collect($m['test_cases'])->filter(fn($tc) => !$tc['exists'])->count());
                $updateTc = collect($parsed)->sum(fn($m) => collect($m['test_cases'])->filter(fn($tc) => $tc['exists'])->count());
            @endphp

            {{-- Summary badges --}}
            <div class="flex gap-4">
                <div class="flex-1 bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <div class="text-2xl font-extrabold text-brand-teal">{{ $newModules }}</div>
                    <div class="text-xs text-gray-500 mt-1">New Modules</div>
                </div>
                <div class="flex-1 bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <div class="text-2xl font-extrabold text-yellow-500">{{ $updateModules }}</div>
                    <div class="text-xs text-gray-500 mt-1">Updated Modules</div>
                </div>
                <div class="flex-1 bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <div class="text-2xl font-extrabold text-brand-teal">{{ $newTc }}</div>
                    <div class="text-xs text-gray-500 mt-1">New Test Cases</div>
                </div>
                <div class="flex-1 bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <div class="text-2xl font-extrabold text-yellow-500">{{ $updateTc }}</div>
                    <div class="text-xs text-gray-500 mt-1">Updated Test Cases</div>
                </div>
            </div>

            {{-- Preview table --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-brand-dark">Import Preview Details</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Existing modules will only have their display_name &amp; description updated. Spec will not be changed.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Module (Topic)</th>
                                <th class="px-5 py-3 text-left">Sheet</th>
                                <th class="px-5 py-3 text-left">Module Key</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-center">Test Cases</th>
                                <th class="px-5 py-3 text-center">Total Steps</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($parsed as $moduleKey => $moduleData)
                            @php
                                $tcNew = collect($moduleData['test_cases'])->filter(fn($tc) => !$tc['exists'])->count();
                                $tcUpd = collect($moduleData['test_cases'])->filter(fn($tc) => $tc['exists'])->count();
                                $totalSteps = collect($moduleData['test_cases'])->sum(fn($tc) => count($tc['parameters']['steps']));
                            @endphp
                            <tr x-data="{ open: false }">
                                <td class="px-5 py-3">
                                    <button @click="open = !open" class="text-left font-semibold text-brand-dark hover:text-brand-teal text-sm">
                                        <span x-text="open ? '▾' : '▸'" class="mr-1 text-gray-400"></span>
                                        {{ $moduleData['display_name'] }}
                                    </button>
                                    <p class="text-xs text-gray-400 ml-4">{{ $moduleData['description'] }}</p>
                                    {{-- Nested test case list --}}
                                    <div x-show="open" x-cloak class="mt-2 ml-4 space-y-1">
                                        @foreach($moduleData['test_cases'] as $tc)
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">TC-{{ $tc['test_case_id'] }}</span>
                                            <span class="text-gray-600 truncate max-w-xs">{{ $tc['notes'] }}</span>
                                            @if($tc['exists'])
                                                <span class="text-yellow-600 font-medium">update</span>
                                            @else
                                                <span class="text-green-600 font-medium">new</span>
                                            @endif
                                            <span class="text-gray-400">{{ count($tc['parameters']['steps']) }} steps</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ $moduleData['sheet'] }}</td>
                                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $moduleData['module_key'] }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if($moduleData['exists'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Update</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">New</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center text-sm">
                                    @if($tcNew > 0)
                                        <span class="text-green-600 font-semibold">+{{ $tcNew }}</span>
                                    @endif
                                    @if($tcUpd > 0)
                                        <span class="text-yellow-600 font-semibold ml-1">~{{ $tcUpd }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center text-sm text-gray-500">{{ $totalSteps }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('test-suite.import.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                    ← Upload Again
                </a>
                <form method="POST" action="{{ route('test-suite.import.confirm') }}">
                    @csrf
                    <button type="submit"
                        class="px-6 py-2.5 bg-brand-teal text-white rounded-lg text-sm font-semibold hover:opacity-90 transition"
                        onclick="return confirm('Proceed to import {{ count($parsed) }} modules and {{ $totalTc }} test cases?')">
                        Confirm Import
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
