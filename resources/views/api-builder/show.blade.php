<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('test-specs.index') }}" class="text-gray-400 hover:text-brand-dark transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-brand-dark leading-tight">{{ $testSpec->display_name }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">API Record Builder</p>
            </div>
        </div>
    </x-slot>

    <div
        x-data="apiBuilder()"
        x-init="init()"
        class="py-8 bg-gray-50 min-h-screen"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-6 items-start">

                {{-- ── LEFT: Builder panel ──────────────────────────────────────────── --}}
                <div class="flex-1 min-w-0 space-y-4">

                    {{-- Steps --}}
                    <template x-for="(step, stepIndex) in steps" :key="step._uid">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            {{-- Step header --}}
                            <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wide" x-text="'Step ' + (stepIndex + 1)"></span>
                                <div class="flex items-center gap-2 ml-auto">
                                    <button @click="moveStep(stepIndex, -1)" :disabled="stepIndex === 0"
                                        class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30 transition" title="Move up">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button @click="moveStep(stepIndex, 1)" :disabled="stepIndex === steps.length - 1"
                                        class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30 transition" title="Move down">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button @click="removeStep(stepIndex)"
                                        class="p-1 text-red-400 hover:text-red-600 transition" title="Remove step">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="px-5 py-4 space-y-4">
                                {{-- Object + Operation --}}
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Object</label>
                                        <select x-model="step.object" @change="onObjectChange(step)"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal">
                                            <option value="">— Select object —</option>
                                            @foreach($objects as $obj)
                                                <option value="{{ $obj->api_name }}">{{ $obj->label }} ({{ $obj->api_name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Operation</label>
                                        <select x-model="step.operation"
                                            class="text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal">
                                            <option value="create">Create</option>
                                            <option value="update">Update</option>
                                            <option value="query">Query</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Record ID (update only) --}}
                                <template x-if="step.operation === 'update'">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Record ID</label>
                                        <input type="text" x-model="step.record_id"
                                            placeholder="e.g. @{{opportunityId}} or paste ID"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal font-mono" />
                                    </div>
                                </template>

                                {{-- Query inputs --}}
                                <template x-if="step.operation === 'query'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">SELECT fields</label>
                                            <input type="text" x-model="step.select_fields"
                                                placeholder="Id, Name, Status__c"
                                                class="w-full text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal" />
                                            <p class="text-xs text-gray-400 mt-0.5">Comma-separated API names</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">WHERE clause</label>
                                            <input type="text" x-model="step.where_clause"
                                                placeholder="Name = 'Test Corp' AND IsActive__c = true"
                                                class="w-full text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal" />
                                            <p class="text-xs text-gray-400 mt-0.5">Leave blank for no filter. Supports @{{runtimeKey}} templates.</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">LIMIT</label>
                                            <input type="number" x-model.number="step.query_limit" min="1" max="200"
                                                class="w-32 text-sm border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal" />
                                        </div>
                                    </div>
                                </template>

                                {{-- Fields (create / update only) --}}
                                <div x-show="step.operation !== 'query'" class="space-y-2">
                                    <template x-for="(field, fieldIndex) in step.fields" :key="field._uid">
                                        <div class="flex gap-2 items-start p-3 bg-gray-50 rounded-xl">
                                            {{-- Field selector (searchable) --}}
                                            <div class="w-52 shrink-0"
                                                 x-data="{
                                                     open: false,
                                                     search: '',
                                                     pos: { top: 0, left: 0, width: 0 },
                                                     toggle() {
                                                         this.open = !this.open;
                                                         if (this.open) {
                                                             const r = this.$refs.trigger.getBoundingClientRect();
                                                             this.pos = { top: r.bottom + 4, left: r.left, width: Math.max(r.width, 260) };
                                                             this.$nextTick(() => this.$refs.fsearch && this.$refs.fsearch.focus());
                                                         } else {
                                                             this.search = '';
                                                         }
                                                     },
                                                     select(apiName) {
                                                         field.api_name = apiName;
                                                         onFieldChange(step, field, true);
                                                         this.open = false;
                                                         this.search = '';
                                                     },
                                                     get filtered() {
                                                         const q = this.search.toLowerCase();
                                                         return getObjectFields(step.object).filter(f =>
                                                             !q || f.label.toLowerCase().includes(q) || f.api_name.toLowerCase().includes(q)
                                                         );
                                                     },
                                                     get selectedLabel() {
                                                         if (!field.api_name) return '— Field —';
                                                         const f = getObjectFields(step.object).find(f => f.api_name === field.api_name);
                                                         return f ? f.label + ' (' + f.api_name + ')' : field.api_name;
                                                     }
                                                 }">
                                                <button type="button" x-ref="trigger"
                                                    @click="toggle()"
                                                    @scroll.window="open = false"
                                                    class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 text-left bg-white flex items-center justify-between gap-1 hover:border-brand-teal focus:outline-none focus:border-brand-teal transition">
                                                    <span class="truncate" :class="field.api_name ? 'text-gray-700' : 'text-gray-400'" x-text="selectedLabel"></span>
                                                    <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>

                                                <template x-teleport="body">
                                                    <div x-show="open"
                                                         @click.outside="open = false"
                                                         @keydown.escape.window="open = false"
                                                         :style="`position:fixed;top:${pos.top}px;left:${pos.left}px;width:${pos.width}px;z-index:9999`"
                                                         class="bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                                                        <div class="p-2 border-b border-gray-100">
                                                            <input type="text" x-model="search" x-ref="fsearch"
                                                                   placeholder="Search fields…"
                                                                   class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-brand-teal" />
                                                        </div>
                                                        <ul class="max-h-56 overflow-y-auto py-1">
                                                            <li>
                                                                <button type="button" @click="select('')"
                                                                    class="w-full text-left px-3 py-1.5 text-xs text-gray-400 hover:bg-gray-50">— Field —</button>
                                                            </li>
                                                            <template x-for="f in filtered" :key="f.api_name">
                                                                <li>
                                                                    <button type="button" @click="select(f.api_name)"
                                                                        :class="f.api_name === field.api_name ? 'bg-teal-50 text-brand-teal font-medium' : 'text-gray-700 hover:bg-gray-50'"
                                                                        class="w-full text-left px-3 py-1.5 text-xs flex items-center justify-between gap-2">
                                                                        <span class="truncate" x-text="f.label"></span>
                                                                        <span class="text-gray-400 font-mono shrink-0" x-text="f.api_name"></span>
                                                                    </button>
                                                                </li>
                                                            </template>
                                                            <li x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400">No fields match</li>
                                                        </ul>
                                                    </div>
                                                </template>

                                                <span x-show="field.type"
                                                    class="mt-1 inline-block text-xs px-1.5 py-0.5 rounded bg-purple-50 text-purple-600 font-mono"
                                                    x-text="field.type"></span>
                                            </div>

                                            {{-- Value area --}}
                                            <div class="flex-1 space-y-1.5">
                                                {{-- Reference field controls --}}
                                                <template x-if="field.type === 'reference'">
                                                    <div class="space-y-1.5">
                                                        <div class="flex gap-1.5">
                                                            <button @click="field.lookup_mode = 'id'"
                                                                :class="field.lookup_mode === 'id' ? 'bg-brand-teal text-white' : 'bg-white text-gray-500 border border-gray-300'"
                                                                class="text-xs px-2 py-0.5 rounded-md font-medium transition">ID</button>
                                                            <button @click="field.lookup_mode = 'name'"
                                                                :class="field.lookup_mode === 'name' ? 'bg-brand-teal text-white' : 'bg-white text-gray-500 border border-gray-300'"
                                                                class="text-xs px-2 py-0.5 rounded-md font-medium transition">Lookup by Name</button>
                                                            <template x-if="stepIndex > 0">
                                                                <button @click="
                                                                        const prev = steps[stepIndex - 1];
                                                                        const refPath = prev.operation === 'query' ? '.records[0].Id' : '.id';
                                                                        field.lookup_mode = 'ref';
                                                                        field.value = '@{' + prev.reference_id + refPath + '}';
                                                                    "
                                                                    :class="field.lookup_mode === 'ref' ? 'bg-indigo-500 text-white' : 'bg-white text-gray-500 border border-gray-300'"
                                                                    class="text-xs px-2 py-0.5 rounded-md font-medium transition"
                                                                    x-text="'← Step ' + stepIndex + ' ID'"></button>
                                                            </template>
                                                        </div>

                                                        {{-- ID mode --}}
                                                        <template x-if="field.lookup_mode === 'id'">
                                                            <div>
                                                                <input type="text" x-model="field.value"
                                                                    placeholder="ID or @{{runtimeKey}}"
                                                                    class="w-full text-xs border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal font-mono" />
                                                                <p class="text-xs text-gray-400 mt-0.5">Available: {{ $runtimeKeys->implode(', ') }}</p>
                                                            </div>
                                                        </template>

                                                        {{-- Name lookup mode --}}
                                                        <template x-if="field.lookup_mode === 'name'">
                                                            <div class="flex gap-1.5">
                                                                <input type="text" x-model="field.value"
                                                                    :placeholder="'Name in ' + (field.referenced_to || 'object')"
                                                                    class="flex-1 text-xs border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal" />
                                                                <button @click="previewLookup(field)"
                                                                    class="text-xs px-2.5 py-1 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 transition whitespace-nowrap">
                                                                    Look up
                                                                </button>
                                                                <span x-show="field._lookupResult" class="text-xs text-green-600 font-mono self-center truncate max-w-32" x-text="field._lookupResult"></span>
                                                                <span x-show="field._lookupError" class="text-xs text-red-500 self-center" x-text="field._lookupError"></span>
                                                            </div>
                                                        </template>

                                                        {{-- Ref mode (auto-filled) --}}
                                                        <template x-if="field.lookup_mode === 'ref'">
                                                            <input type="text" x-model="field.value" readonly
                                                                class="w-full text-xs border-indigo-200 bg-indigo-50 rounded-lg font-mono text-indigo-700 cursor-default" />
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Picklist dropdown --}}
                                                <template x-if="field.type === 'picklist' || field.type === 'multipicklist'">
                                                    <div>
                                                        <template x-if="field.picklist_values && field.picklist_values.length > 0">
                                                            <select x-model="field.value"
                                                                class="w-full text-xs border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal">
                                                                <option value="">— Select —</option>
                                                                <template x-for="opt in field.picklist_values" :key="opt.value">
                                                                    <option :value="opt.value" :selected="opt.value === field.value" x-text="opt.label"></option>
                                                                </template>
                                                            </select>
                                                        </template>
                                                        <template x-if="!field.picklist_values || field.picklist_values.length === 0">
                                                            <div class="space-y-1">
                                                                <input type="text" x-model="field.value"
                                                                    placeholder="Type value manually"
                                                                    class="w-full text-xs border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal" />
                                                                <p class="text-xs text-amber-500">
                                                                    No options loaded — re-sync this object via
                                                                    <a href="{{ route('object-sync.index') }}" target="_blank" class="underline">Object Sync</a>
                                                                    to populate the dropdown.
                                                                </p>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Plain text value (all other types) --}}
                                                <template x-if="field.type !== 'reference' && field.type !== 'picklist' && field.type !== 'multipicklist'">
                                                    <input type="text" x-model="field.value"
                                                        placeholder="Value or @{{runtimeKey}}"
                                                        class="w-full text-xs border-gray-300 rounded-lg focus:border-brand-teal focus:ring-brand-teal" />
                                                </template>
                                            </div>

                                            {{-- Remove field --}}
                                            <button @click="removeField(step, fieldIndex)"
                                                class="mt-1 text-red-400 hover:text-red-600 transition shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>

                                    <button @click="addField(step)" :disabled="!step.object"
                                        class="text-xs text-brand-teal hover:underline font-medium disabled:opacity-40 disabled:no-underline">
                                        + Add Field
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Add Object + Save --}}
                    <div class="flex items-center gap-3">
                        <button @click="addStep()"
                            class="text-sm px-4 py-2 border-2 border-dashed border-gray-300 text-gray-500 rounded-xl hover:border-brand-teal hover:text-brand-teal transition w-full font-medium">
                            + Add Object
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button @click="saveConfig()" :disabled="saving"
                            class="px-5 py-2 bg-brand-dark text-white text-sm font-semibold rounded-xl hover:bg-black transition disabled:opacity-60">
                            <span x-show="!saving">Save Configuration</span>
                            <span x-show="saving">Saving…</span>
                        </button>
                    </div>

                    <div x-show="saveMessage" x-transition class="text-sm text-center"
                        :class="saveError ? 'text-red-500' : 'text-green-600'" x-text="saveMessage"></div>

                </div>

                {{-- ── RIGHT: Execute panel ─────────────────────────────────────────── --}}
                <div class="flex-none w-96 sticky top-6">

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-brand-dark">Execute</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                :class="steps.length > 1 ? 'bg-indigo-50 text-indigo-700' : 'bg-teal-50 text-brand-teal'"
                                x-text="steps.length > 1 ? 'Composite API' : 'REST API'"></span>
                        </div>

                        <p class="text-xs text-gray-400 leading-relaxed">
                            <template x-if="steps.length > 1">
                                <span>Multiple steps will use Salesforce Composite API. All steps run atomically — a failure in any step rolls back the others.</span>
                            </template>
                            <template x-if="steps.length <= 1">
                                <span>Single step uses a direct REST API call.</span>
                            </template>
                        </p>

                        <button @click="executeSteps()" :disabled="loading || steps.length === 0"
                            class="w-full py-2.5 bg-brand-teal text-white text-sm font-semibold rounded-xl hover:opacity-90 transition disabled:opacity-50">
                            <span x-show="!loading">Run API Calls</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Running…
                            </span>
                        </button>

                        {{-- Results (inside the card, scrollable) --}}
                        <div x-show="results.length > 0" class="border-t border-gray-100 pt-4 space-y-3 max-h-[60vh] overflow-y-auto">
                            <template x-for="(res, i) in results" :key="i">
                                <div class="rounded-xl border overflow-hidden"
                                    :class="res.success ? 'border-green-200' : 'border-red-200'">
                                    <div class="flex items-center gap-2 px-3 py-2 border-b"
                                        :class="res.success ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'">
                                        <div class="w-2 h-2 rounded-full shrink-0" :class="res.success ? 'bg-green-500' : 'bg-red-500'"></div>
                                        <span class="text-xs font-bold truncate" :class="res.success ? 'text-green-700' : 'text-red-700'"
                                            x-text="'Step ' + (i + 1) + ': ' + res.object + ' ' + res.operation"></span>
                                        <span class="ml-auto text-xs font-mono text-gray-400 shrink-0" x-text="res.status"></span>
                                    </div>
                                    <div class="px-3 py-2.5 space-y-1.5">
                                        {{-- Query result --}}
                                        <template x-if="res.operation === 'query'">
                                            <div class="space-y-1.5">
                                                <p class="text-xs text-gray-400">Records returned: <span class="font-semibold text-brand-dark" x-text="res.total_size ?? 0"></span></p>
                                                <template x-if="res.records && res.records.length > 0">
                                                    <pre class="text-xs text-gray-600 whitespace-pre-wrap break-all max-h-40 overflow-y-auto bg-gray-50 rounded-lg p-2"
                                                        x-text="JSON.stringify(res.records, null, 2)"></pre>
                                                </template>
                                            </div>
                                        </template>
                                        {{-- Create / update result --}}
                                        <template x-if="res.operation !== 'query' && res.id">
                                            <div>
                                                <p class="text-xs text-gray-400">Record ID</p>
                                                <p class="text-xs font-mono text-brand-dark break-all" x-text="res.id"></p>
                                            </div>
                                        </template>
                                        <template x-if="res.body && !res.success">
                                            <div>
                                                <p class="text-xs text-gray-400">Error</p>
                                                <pre class="text-xs text-red-600 whitespace-pre-wrap break-all" x-text="JSON.stringify(res.body, null, 2)"></pre>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    @php
        $objectsJson = $objects->map(fn($o) => [
            'api_name' => $o->api_name,
            'label'    => $o->label,
            'fields'   => $o->fields->map(fn($f) => [
                'api_name'       => $f->api_name,
                'label'          => $f->label,
                'type'           => $f->type,
                'referenced_to'  => $f->referenced_to,
                'picklist_values' => $f->picklist_values ?? [],
            ])->values()->all(),
        ])->values()->all();
        $savedConfig = $testSpec->api_config ?? ['steps' => []];
    @endphp

    <script>
        const _OBJECTS      = @json($objectsJson);
        const _SAVED_CONFIG = @json($savedConfig);
        const _CSRF         = '{{ csrf_token() }}';
        const _SAVE_URL     = '{{ route('api-builder.config', $testSpec) }}';
        const _EXEC_URL     = '{{ route('api-builder.execute', $testSpec) }}';
        const _LOOKUP_URL   = '{{ route('api-builder.lookup') }}';

        let _uid = 0;
        function uid() { return ++_uid; }

        function makeField(overrides = {}) {
            return { _uid: uid(), api_name: '', label: '', type: '', referenced_to: '', picklist_values: [], lookup_mode: 'id', value: '', _lookupResult: '', _lookupError: '', ...overrides };
        }

        function makeStep(overrides = {}) {
            return { _uid: uid(), reference_id: '', object: '', operation: 'create', record_id: '', fields: [],
                     select_fields: 'Id, Name', where_clause: '', query_limit: 10, ...overrides };
        }

        function apiBuilder() {
            return {
                steps: [],
                results: [],
                loading: false,
                saving: false,
                saveMessage: '',
                saveError: false,

                init() {
                    const cfg = _SAVED_CONFIG;
                    this.steps = (cfg.steps || []).map((s, si) => makeStep({
                        ...s,
                        _uid: uid(),
                        reference_id: s.reference_id || ('step_' + si),
                        fields: (s.fields || []).map(f => makeField(f)),
                    }));
                },

                getObjectFields(apiName) {
                    const obj = _OBJECTS.find(o => o.api_name === apiName);
                    if (!obj) return [];
                    return [...obj.fields].sort((a, b) => a.label.localeCompare(b.label));
                },

                onObjectChange(step) {
                    step.fields = [];
                    step.reference_id = 'step_' + this.steps.indexOf(step);
                },

                onFieldChange(step, field, resetValue = true) {
                    const fields = this.getObjectFields(step.object);
                    const meta = fields.find(f => f.api_name === field.api_name);
                    if (meta) {
                        field.label           = meta.label;
                        field.type            = meta.type;
                        field.referenced_to   = meta.referenced_to || '';
                        field.picklist_values = meta.picklist_values || [];
                        if (resetValue) {
                            field.lookup_mode = 'id';
                            field.value       = '';
                        }
                    }
                },

                addStep() {
                    const idx = this.steps.length;
                    this.steps.push(makeStep({ reference_id: 'step_' + idx }));
                },

                removeStep(index) {
                    this.steps.splice(index, 1);
                    this.steps.forEach((s, i) => { if (!s.reference_id || s.reference_id.startsWith('step_')) s.reference_id = 'step_' + i; });
                },

                moveStep(index, dir) {
                    const target = index + dir;
                    if (target < 0 || target >= this.steps.length) return;
                    [this.steps[index], this.steps[target]] = [this.steps[target], this.steps[index]];
                    this.steps = [...this.steps];
                },

                addField(step) {
                    step.fields.push(makeField());
                },

                removeField(step, index) {
                    step.fields.splice(index, 1);
                },

                async previewLookup(field) {
                    field._lookupResult = '';
                    field._lookupError  = '';
                    if (!field.referenced_to || !field.value) { field._lookupError = 'Need object + name'; return; }
                    try {
                        const url = _LOOKUP_URL + '?object=' + encodeURIComponent(field.referenced_to) + '&name=' + encodeURIComponent(field.value);
                        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await res.json();
                        if (data.id) { field._lookupResult = data.id; }
                        else { field._lookupError = data.error || 'Not found'; }
                    } catch { field._lookupError = 'Request failed'; }
                },

                buildConfig() {
                    return {
                        steps: this.steps.map((s, i) => ({
                            reference_id:  s.reference_id || ('step_' + i),
                            object:        s.object,
                            operation:     s.operation,
                            record_id:     s.record_id || '',
                            select_fields: s.select_fields || 'Id, Name',
                            where_clause:  s.where_clause || '',
                            query_limit:   s.query_limit || 10,
                            fields: s.fields.map(f => ({
                                api_name:        f.api_name,
                                label:           f.label,
                                type:            f.type,
                                referenced_to:   f.referenced_to || '',
                                picklist_values: f.picklist_values || [],
                                lookup_mode:     f.lookup_mode || 'id',
                                value:           f.value || '',
                            })),
                        })),
                    };
                },

                async saveConfig() {
                    this.saving = true;
                    this.saveMessage = '';
                    try {
                        const res = await fetch(_SAVE_URL, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _CSRF },
                            body: JSON.stringify({ config: this.buildConfig() }),
                        });
                        const data = await res.json();
                        this.saveError   = !data.success;
                        this.saveMessage = data.success ? 'Configuration saved.' : 'Failed to save.';
                    } catch { this.saveError = true; this.saveMessage = 'Request failed.'; }
                    finally { this.saving = false; setTimeout(() => { this.saveMessage = ''; }, 3000); }
                },

                async executeSteps() {
                    this.loading = true;
                    this.results = [];
                    try {
                        const res = await fetch(_EXEC_URL, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _CSRF },
                            body: JSON.stringify({ config: this.buildConfig() }),
                        });
                        const data = await res.json();
                        this.results = data.steps || [];
                    } catch { this.results = [{ success: false, object: 'Request', operation: '', status: 0, id: null, body: { message: 'Network error' } }]; }
                    finally { this.loading = false; }
                },
            };
        }
    </script>

</x-app-layout>
