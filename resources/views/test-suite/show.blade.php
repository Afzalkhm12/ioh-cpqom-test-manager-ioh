<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight flex items-center gap-2">
            <a href="{{ route('test-suite.index') }}" class="text-gray-400 hover:text-brand-dark transition-colors">Test Suite</a>
            <span class="text-gray-300">/</span>
            {{ $testModule->display_name }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">{{ $errors->first() }}</div>
            @endif

            {{-- ── Module Header ─────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $testModule->module_key }}</span>
                    <h3 class="text-xl font-bold text-brand-dark mt-1">{{ $testModule->display_name }}</h3>
                    @if($testModule->description)
                        <p class="text-sm text-gray-500 mt-1">{{ $testModule->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-extrabold text-brand-teal">{{ $testModule->counter }}</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Run Counter</div>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('test-suite.counter.increment', $testModule) }}">
                            @csrf
                            <button class="px-3 py-2 bg-brand-teal text-white text-sm rounded-lg font-semibold hover:opacity-90 transition">+1</button>
                        </form>
                        <form method="POST" action="{{ route('test-suite.counter.reset', $testModule) }}"
                            onsubmit="return confirm('Reset to 0?')">
                            @csrf
                            <button class="px-3 py-2 border border-gray-300 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">Reset</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Spec Runner ───────────────────────────────────────────── --}}
            <div x-data="{
                    running: false,
                    result: null,
                    hasSpec: {{ $testModule->spec ? 'true' : 'false' }},
                    async run() {
                        this.running = true;
                        this.result = null;
                        try {
                            const res = await fetch('{{ route('test-suite.run', $testModule) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                },
                            });
                            this.result = await res.json();
                        } catch (e) {
                            this.result = { error: e.message };
                        } finally {
                            this.running = false;
                        }
                    }
                }"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-brand-dark">Spec File</h4>
                            <a href="{{ route('test-specs.index') }}"
                                class="text-xs text-gray-400 hover:text-brand-teal transition">Manage specs →</a>
                        </div>

                        <form method="POST" action="{{ route('test-suite.spec.update', $testModule) }}"
                            class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <select name="spec_id"
                                class="flex-1 text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal py-1.5"
                                onchange="this.form.submit()">
                                <option value="">— None —</option>
                                @foreach($specs as $spec)
                                    <option value="{{ $spec->id }}"
                                        {{ $testModule->spec_id == $spec->id ? 'selected' : '' }}>
                                        {{ $spec->display_name }}
                                        ({{ $spec->runner_key }})
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        @if($testModule->spec)
                            <p class="mt-2 text-xs font-mono text-gray-400">{{ $testModule->spec->file_path }}</p>
                        @endif
                    </div>

                    <a href="{{ route('test-suite.api-test.show', $testModule) }}"
                       class="px-4 py-2 border border-brand-teal text-brand-teal text-sm font-semibold rounded-lg hover:bg-teal-50 transition flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        API Test
                    </a>

                    <button @click="run()" :disabled="running || !hasSpec"
                        class="px-4 py-2 bg-brand-teal text-white text-sm font-semibold rounded-lg hover:opacity-90 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 shrink-0">
                        <svg x-show="!running" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/>
                        </svg>
                        <svg x-show="running" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="running ? 'Running…' : 'Run'"></span>
                    </button>
                </div>

                {{-- Result --}}
                <div x-show="result" x-cloak class="mt-4 pt-4 border-t border-gray-100">
                    <div :class="result?.error || result?.status >= 400 ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800'"
                        class="rounded-lg border p-4 text-xs font-mono whitespace-pre-wrap overflow-x-auto"
                        x-text="JSON.stringify(result, null, 2)">
                    </div>
                </div>
            </div>

            {{-- ── Test Cases Accordion ──────────────────────────────────── --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-brand-dark">Test Cases ({{ $testModule->testParameters->count() }})</h3>
                    <button onclick="document.getElementById('add-tc-modal').classList.remove('hidden')"
                        class="text-xs px-3 py-1.5 bg-brand-teal text-white rounded-lg font-semibold hover:opacity-90 transition">
                        + Add Test Case
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($testModule->testParameters->sortBy('test_case_id') as $tp)
                    <div x-data="{
                            open: false,
                            editing: false,
                            disabled: {},
                            newRows: [],
                            addRow() { this.newRows.push({ key: '', value: '' }); },
                            removeRow(i) { this.newRows.splice(i, 1); }
                        }"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                        {{-- Accordion Header --}}
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition text-left">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-mono font-bold text-brand-teal bg-teal-50 px-2 py-0.5 rounded">{{ $tp->test_case_id }}</span>
                                <span class="text-sm font-semibold text-brand-dark">
                                    {{ count($tp->parameters ?? []) }} parameter{{ count($tp->parameters ?? []) !== 1 ? 's' : '' }}
                                </span>
                                @if($tp->notes)
                                    <span class="text-xs text-gray-400 italic">— {{ Str::limit($tp->notes, 60) }}</span>
                                @endif
                            </div>
                            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Accordion Body --}}
                        <div x-show="open" x-collapse class="border-t border-gray-100">
                            <div class="px-5 py-4">

                                {{-- Parameter Summary Table --}}
                                <div x-show="!editing">
                                    @if(!empty($tp->parameters))
                                    <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                                        <table class="min-w-full text-xs divide-y divide-gray-100">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase tracking-wide w-1/3">Parameter</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase tracking-wide">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                @foreach($tp->parameters as $key => $value)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 font-mono text-gray-600 font-medium">{{ $key }}</td>
                                                    <td class="px-4 py-2 text-gray-800">
                                                        @if(is_array($value))
                                                            <span class="font-mono text-purple-600">{{ json_encode($value) }}</span>
                                                        @elseif(is_numeric($value))
                                                            <span class="font-mono text-blue-600">{{ $value }}</span>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-sm text-gray-400 italic mb-4">No parameters defined yet.</p>
                                    @endif

                                    <div class="flex gap-2">
                                        <button @click="editing = true; newRows = []"
                                            class="text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                                            ✏️ Edit Parameters
                                        </button>
                                        <form method="POST" action="{{ route('test-suite.parameters.destroy', $tp) }}"
                                            onsubmit="return confirm('Delete {{ $tp->test_case_id }}?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs px-3 py-1.5 bg-red-50 text-red-600 rounded-lg font-medium hover:bg-red-100 transition">
                                                🗑 Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Per-field Editor --}}
                                <div x-show="editing">
                                    <form method="POST" action="{{ route('test-suite.parameters.update', $tp) }}" class="space-y-4">
                                        @csrf @method('PUT')

                                        {{-- Existing params as individual inputs --}}
                                        <div class="rounded-xl border border-gray-200 overflow-hidden">
                                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Parameters</span>
                                                <button type="button" @click="addRow()"
                                                    class="text-xs px-2 py-1 bg-brand-teal text-white rounded font-semibold hover:opacity-90 transition">
                                                    + Add Field
                                                </button>
                                            </div>

                                            <div class="divide-y divide-gray-100">
                                                {{-- Existing key-value rows --}}
                                                @foreach($tp->parameters ?? [] as $key => $value)
                                                @php
                                                    $isNested    = is_array($value) && collect($value)->contains(fn($v) => is_array($v));
                                                    $isFlatArray = is_array($value) && !$isNested;
                                                    $displayValue = $isNested
                                                        ? json_encode($value)
                                                        : ($isFlatArray ? implode(', ', $value) : $value);
                                                @endphp
                                                <div class="flex items-center gap-3 px-4 py-2.5"
                                                    x-show="!disabled['{{ $key }}']">
                                                    <span class="w-1/3 font-mono text-xs text-gray-600 font-semibold shrink-0 truncate"
                                                        title="{{ $key }}">{{ $key }}</span>
                                                    <input
                                                        type="text"
                                                        name="parameters[{{ $key }}]"
                                                        value="{{ $displayValue }}"
                                                        :disabled="disabled['{{ $key }}']"
                                                        @if($isNested) readonly @endif
                                                        class="flex-1 text-sm border-gray-200 rounded-lg focus:border-brand-teal focus:ring-brand-teal py-1.5 {{ $isNested ? 'font-mono text-xs bg-gray-50 text-gray-400 cursor-default' : '' }}"
                                                        @if($isFlatArray)
                                                        title="Array: separate multiple values with a comma"
                                                        placeholder="val1, val2, …"
                                                        @endif
                                                    >
                                                    @if($isNested)
                                                        <span class="text-xs text-gray-300 shrink-0">json</span>
                                                    @elseif($isFlatArray)
                                                        <span class="text-xs text-purple-400 shrink-0">array</span>
                                                    @elseif(is_numeric($value))
                                                        <span class="text-xs text-blue-400 shrink-0">num</span>
                                                    @else
                                                        <span class="text-xs text-gray-300 shrink-0">str</span>
                                                    @endif
                                                    <button type="button"
                                                        @click="disabled['{{ $key }}'] = true"
                                                        class="text-gray-300 hover:text-red-400 transition shrink-0"
                                                        title="Remove this field">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                @endforeach

                                                {{-- New rows added dynamically --}}
                                                <template x-for="(row, i) in newRows" :key="i">
                                                    <div class="flex items-center gap-3 px-4 py-2.5 bg-teal-50">
                                                        <input type="text" name="new_keys[]"
                                                            x-model="row.key"
                                                            placeholder="fieldName"
                                                            class="w-1/3 font-mono text-xs border-gray-200 rounded-lg focus:border-brand-teal focus:ring-brand-teal py-1.5 shrink-0">
                                                        <input type="text" name="new_values[]"
                                                            x-model="row.value"
                                                            placeholder="value"
                                                            class="flex-1 text-sm border-gray-200 rounded-lg focus:border-brand-teal focus:ring-brand-teal py-1.5">
                                                        <span class="text-xs text-teal-400 shrink-0">new</span>
                                                        <button type="button" @click="removeRow(i)"
                                                            class="text-gray-300 hover:text-red-400 transition shrink-0">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>

                                                {{-- Empty state --}}
                                                <div x-show="Object.keys({{ json_encode((object)($tp->parameters ?? [])) }}).length === 0 && newRows.length === 0"
                                                    class="px-4 py-6 text-center text-xs text-gray-400 italic">
                                                    No parameters yet. Click "+ Add Field" to begin.
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Notes --}}
                                        <div>
                                            <x-input-label value="Notes (optional)" />
                                            <x-text-input type="text" name="notes" value="{{ $tp->notes }}"
                                                class="mt-1 block w-full text-sm" placeholder="Brief description…" />
                                        </div>

                                        <div class="flex gap-2">
                                            <button type="submit"
                                                class="text-xs px-4 py-1.5 bg-brand-teal text-white rounded-lg font-semibold hover:opacity-90 transition">
                                                💾 Save Changes
                                            </button>
                                            <button type="button" @click="editing = false; disabled = {}; newRows = []"
                                                class="text-xs px-3 py-1.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-10 text-center text-gray-400">
                        No test cases yet. Add one above.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Add Test Case Modal --}}
    <div id="add-tc-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
            onclick="document.getElementById('add-tc-modal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 max-h-[85vh] overflow-y-auto">
            <h3 class="font-bold text-lg mb-4 text-brand-dark">Add Test Case</h3>
            <form method="POST" action="{{ route('test-suite.parameters.store', $testModule) }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="test_case_id" value="Test Case ID" />
                    <x-text-input id="test_case_id" name="test_case_id" type="text" class="mt-1 block w-full font-mono"
                        placeholder="e.g. tc003" required />
                </div>
                <div>
                    <x-input-label for="tc_parameters" value="Parameters (JSON)" />
                    <textarea id="tc_parameters" name="parameters" rows="10"
                        class="mt-1 block w-full font-mono text-xs border-gray-300 focus:border-brand-teal focus:ring-brand-teal rounded-lg shadow-sm"
                        placeholder="{&#10;  &quot;accountName&quot;: &quot;Test CA&quot;&#10;}"></textarea>
                </div>
                <div>
                    <x-input-label for="tc_notes" value="Notes (optional)" />
                    <x-text-input id="tc_notes" name="notes" type="text" class="mt-1 block w-full" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('add-tc-modal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-brand-teal text-white rounded-lg text-sm font-semibold hover:opacity-90">Add</button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
