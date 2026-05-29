<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-brand-dark leading-tight">
                {{ $productTestSuite->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('product-test-suites.edit', $productTestSuite) }}"
                    class="text-xs px-3 py-1.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                    Edit
                </a>
                <a href="{{ route('product-test-suites.index') }}"
                    class="text-xs px-3 py-1.5 border border-gray-200 text-gray-400 rounded-lg hover:bg-gray-50 transition">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen"
        x-data="{
            suiteRunning: false,

            statusColor(status) {
                return {
                    passed:     'bg-green-50 text-green-700 border border-green-300',
                    success:    'bg-green-50 text-green-700 border border-green-300',
                    failed:     'bg-red-500 text-white',
                    not_passed: 'bg-red-50 text-red-700 border border-red-300',
                    error:      'bg-red-500 text-white',
                    aborted:    'bg-yellow-500 text-white',
                    skipped:    'bg-yellow-50 text-yellow-700 border border-yellow-300',
                    running:    'bg-blue-50 text-blue-700 border border-blue-300',
                }[status] ?? 'bg-gray-100 text-gray-600 border border-gray-300';
            },

            statusLabel(status) {
                return { not_passed: 'Not Passed' }[status] ?? status;
            },

            async runSuite() {
                this.suiteRunning = true;
                const rows = document.querySelectorAll('[data-module-row]');
                for (const row of rows) {
                    await row.__x.$data.runModule();
                }
                this.suiteRunning = false;
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Product identity card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs py-0.5 px-2 rounded-full bg-brand-teal/10 text-brand-teal font-semibold">
                                {{ $productTestSuite->product->product_line }}
                            </span>
                            <span class="text-xs font-mono px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">
                                {{ $productTestSuite->product->product_code }}
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-brand-dark">{{ $productTestSuite->product->product_offer }}</p>
                        @if($productTestSuite->description)
                            <p class="text-xs text-gray-400">{{ $productTestSuite->description }}</p>
                        @endif
                    </div>
                    {{-- <button @click="runSuite()" :disabled="suiteRunning"
                        class="shrink-0 px-5 py-2 bg-brand-teal text-white rounded-lg text-sm font-semibold hover:opacity-90 disabled:opacity-60 transition flex items-center gap-2">
                        <span x-show="!suiteRunning">Run Suite</span>
                        <span x-show="suiteRunning" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Running…
                        </span>
                    </button> --}}
                </div>
            </div>

            {{-- Stat tiles --}}
            <div class="grid grid-cols-4 gap-4">
                {{-- Passed --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $passedCount }}</p>
                        <p class="text-xs text-gray-400 font-medium">Passed</p>
                    </div>
                </div>

                {{-- Not Passed --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $notPassedCount }}</p>
                        <p class="text-xs text-gray-400 font-medium">Not Passed</p>
                    </div>
                </div>

                {{-- Error --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $errorCount }}</p>
                        <p class="text-xs text-gray-400 font-medium">Error</p>
                    </div>
                </div>

                {{-- Not Run --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" stroke-dasharray="4 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $notRunCount }}</p>
                        <p class="text-xs text-gray-400 font-medium">Not Run</p>
                    </div>
                </div>
            </div>

            {{-- Module sequence table --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-brand-dark">Module Sequence</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left w-10">#</th>
                            <th class="px-5 py-3 text-left">Module</th>
                            <th class="px-5 py-3 text-left">Last Run</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    @forelse($productTestSuite->modules as $module)
                    @php
                        $lastRun           = $latestRuns->get($module->id);
                        $initialCreatedIds = $lastRun?->created_ids ?? [];
                        $initialValidation = $lastRun?->validation_status ?? null;
                        $initialFinding    = $lastRun?->finding ?? '';
                        $initialLog        = $lastRun?->log ?? null;
                        $initialImages     = $lastRun?->evidence_images
                            ? array_map(fn($p) => \Illuminate\Support\Facades\Storage::url($p), $lastRun->evidence_images)
                            : [];
                        $detailOpen = $lastRun && (
                            $lastRun->created_ids ||
                            $lastRun->validation_status ||
                            $lastRun->finding ||
                            in_array($lastRun->status, ['error', 'aborted'])
                        );
                    @endphp
                    <tbody
                        data-module-row
                        x-data="{
                            runId: {{ $lastRun ? $lastRun->id : 'null' }},
                            status: '{{ $lastRun ? $lastRun->status : 'idle' }}',
                            validationStatus: {{ $initialValidation ? "'$initialValidation'" : 'null' }},
                            createdIds: @js($initialCreatedIds),
                            sfBaseUrl: '{{ $salesforceUrl }}',
                            finding: @js($initialFinding),
                            log: @js($initialLog),
                            evidenceImages: @js($initialImages),
                            result: null,
                            open: {{ $detailOpen ? 'true' : 'false' }},

                            showFindingForm: false,
                            findingDraft: '',
                            previewUrls: [],
                            findingSubmitting: false,

                            async runModule() {
                                this.status = 'running';
                                this.result = null;
                                this.open = false;
                                this.showFindingForm = false;
                                try {
                                    const res = await fetch('{{ route('product-test-suites.run-module', [$productTestSuite, $module]) }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Accept': 'application/json',
                                        }
                                    });
                                    const data = await res.json();
                                    this.runId = data.run_id ?? null;

                                    if (!this.runId || data.status === 'error') {
                                        this.status = data.status ?? 'error';
                                        this.result = data;
                                        this.open = true;
                                        return;
                                    }

                                    this.createdIds = {};
                                    this.validationStatus = null;
                                    this.finding = '';
                                    this.log = null;
                                    this.evidenceImages = [];

                                    await this.pollStatus(this.runId);
                                } catch(e) {
                                    this.status = 'error';
                                    this.result = { status: 'error', error: e.message };
                                    this.open = true;
                                }
                            },

                            async pollStatus(runId) {
                                const terminal = ['success', 'error', 'aborted'];
                                while (true) {
                                    await new Promise(r => setTimeout(r, 2000));
                                    try {
                                        const res = await fetch('/product-test-runs/' + runId, {
                                            headers: { 'Accept': 'application/json' }
                                        });
                                        const data = await res.json();
                                        this.status = data.status;
                                        this.createdIds = data.created_ids ?? {};
                                        this.validationStatus = data.validation_status ?? null;
                                        this.finding = data.finding ?? '';
                                        this.log = data.log ?? null;
                                        this.evidenceImages = data.evidence_images ?? [];
                                        if (terminal.includes(data.status)) {
                                            this.open = data.status !== 'success';
                                            break;
                                        }
                                    } catch(e) {
                                        this.status = 'error';
                                        this.result = { status: 'error', error: e.message };
                                        this.open = true;
                                        break;
                                    }
                                }
                            },

                            async markPassed() {
                                if (!this.runId) return;
                                const res = await fetch('/product-test-runs/' + this.runId, {
                                    method: 'PUT',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ validation_status: 'passed' })
                                });
                                const data = await res.json();
                                this.validationStatus = data.validation_status;
                                this.showFindingForm = false;
                            },

                            openFindingForm() {
                                this.findingDraft = this.finding ?? '';
                                this.previewUrls = [...this.evidenceImages];
                                this.showFindingForm = true;
                                this.open = true;
                            },

                            onFilesChanged(event) {
                                const existing = this.evidenceImages ?? [];
                                this.previewUrls = [...existing];
                                for (const file of event.target.files) {
                                    this.previewUrls.push(URL.createObjectURL(file));
                                }
                            },

                            async submitFinding() {
                                if (!this.runId || this.findingSubmitting) return;
                                this.findingSubmitting = true;
                                const form = new FormData();
                                form.append('finding', this.findingDraft);
                                const fileInput = this.$refs.evidenceInput;
                                if (fileInput && fileInput.files.length) {
                                    for (const file of fileInput.files) {
                                        form.append('images[]', file);
                                    }
                                }
                                try {
                                    const res = await fetch('/product-test-runs/' + this.runId + '/findings', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Accept': 'application/json',
                                        },
                                        body: form,
                                    });
                                    const data = await res.json();
                                    this.validationStatus = data.validation_status;
                                    this.finding = data.finding;
                                    this.evidenceImages = data.evidence_images ?? [];
                                    this.showFindingForm = false;
                                } finally {
                                    this.findingSubmitting = false;
                                }
                            },

                            sfLink(id) {
                                return this.sfBaseUrl + '/' + id;
                            },

                            hasCreatedIds() {
                                return this.createdIds && Object.keys(this.createdIds).length > 0;
                            },

                            createdIdEntries() {
                                if (!this.createdIds) return [];
                                return Object.entries(this.createdIds).flatMap(([key, val]) => {
                                    if (Array.isArray(val)) {
                                        return val.map((v, i) => ({ key: key + '[' + i + ']', value: v }));
                                    }
                                    return [{ key, value: val }];
                                });
                            }
                        }"
                        class="divide-y divide-gray-50 border-t border-gray-100">

                        {{-- Main row --}}
                        <tr :class="status === 'running' ? 'bg-brand-teal/5' : ''">
                            <td class="px-5 py-3 text-xs text-gray-400 font-mono tabular-nums">{{ $module->pivot->sequence_order }}</td>
                            <td class="px-5 py-3 font-semibold text-brand-dark text-sm">{{ $module->display_name }}</td>
                            <td class="px-5 py-3 text-xs text-gray-400 font-mono">
                                <span x-show="runId !== null">Run #<span x-text="runId"></span></span>
                                <span x-show="runId === null" class="text-gray-300">—</span>
                            </td>
                            <td class="px-5 py-3">
                                <span x-show="status === 'running'" class="inline-flex items-center gap-1 text-xs text-brand-teal font-medium">
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                    Running
                                </span>
                                <span x-show="status === 'idle'" class="text-xs text-gray-300 font-medium">—</span>
                                <template x-if="status !== 'idle' && status !== 'running'">
                                    <div class="flex flex-col gap-1 items-start">
                                        <button @click="open = !open"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full font-semibold capitalize cursor-pointer"
                                            :class="$root.statusColor(status)">
                                            <span x-text="$root.statusLabel(status)"></span>
                                            <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <template x-if="validationStatus !== null">
                                            <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-semibold"
                                                :class="validationStatus === 'passed' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                                                <span x-text="validationStatus === 'passed' ? 'Passed ✓' : 'Not Passed ✗'"></span>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button @click="runModule()" :disabled="status === 'running' || suiteRunning"
                                        class="text-xs px-2.5 py-1 bg-brand-teal text-white rounded-lg font-semibold hover:opacity-90 disabled:opacity-40 transition">
                                        Run
                                    </button>
                                    <a href="{{ route('test-suite.show', $module) }}"
                                        class="text-xs text-brand-teal hover:underline font-medium">Open</a>
                                </div>
                            </td>
                        </tr>

                        {{-- Detail / result row --}}
                        <tr x-show="open" x-transition>
                            <td colspan="5" class="px-5 py-5 bg-gray-50 space-y-4">

                                {{-- Run meta --}}
                                <template x-if="runId !== null">
                                    <p class="text-xs text-gray-400">
                                        Run ID: <span class="font-mono text-gray-600" x-text="runId"></span>
                                    </p>
                                </template>

                                {{-- Error / log --}}
                                <template x-if="log">
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Error Log</p>
                                        <p class="text-xs text-red-700 font-mono whitespace-pre-wrap break-all" x-text="log"></p>
                                    </div>
                                </template>

                                {{-- Stale hint (only when no log, no created records, no finding) --}}
                                <template x-if="!log && !result && status !== 'idle' && status !== 'running' && !hasCreatedIds() && !finding">
                                    <p class="text-xs text-gray-400 italic">Run persisted in DB. Reload page to see latest state from Playwright.</p>
                                </template>

                                {{-- Created Records --}}
                                <template x-if="hasCreatedIds()">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Created Records</p>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="entry in createdIdEntries()" :key="entry.key">
                                                <a :href="sfLink(entry.value)" target="_blank"
                                                    class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 bg-white border border-gray-200 rounded-lg hover:border-brand-teal hover:text-brand-teal transition font-mono group">
                                                    <span class="text-gray-500 not-italic font-sans" x-text="entry.key"></span>
                                                    <span x-text="entry.value"></span>
                                                    <svg class="w-3 h-3 text-gray-300 group-hover:text-brand-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Validation buttons --}}
                                <template x-if="status === 'success' || validationStatus !== null">
                                    <div class="flex items-center gap-3">
                                        <p class="text-xs text-gray-500 font-medium">Validation:</p>
                                        <button @click="markPassed()"
                                            class="text-xs px-3 py-1 rounded-lg font-semibold border transition"
                                            :class="validationStatus === 'passed'
                                                ? 'bg-green-500 text-white border-green-500'
                                                : 'bg-white text-green-600 border-green-300 hover:bg-green-50'">
                                            Passed
                                        </button>
                                        <button @click="openFindingForm()"
                                            class="text-xs px-3 py-1 rounded-lg font-semibold border transition"
                                            :class="validationStatus === 'not_passed'
                                                ? 'bg-red-500 text-white border-red-500'
                                                : 'bg-white text-red-600 border-red-300 hover:bg-red-50'">
                                            Not Passed
                                        </button>
                                    </div>
                                </template>

                                {{-- Not Passed finding form --}}
                                <template x-if="showFindingForm">
                                    <div class="border border-red-200 rounded-xl bg-white p-4 space-y-3">
                                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Not Passed — Finding Details</p>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Finding <span class="text-red-500">*</span></label>
                                            <textarea x-model="findingDraft" rows="4"
                                                placeholder="Describe what went wrong, expected vs actual behavior, steps to reproduce…"
                                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Evidence (screenshots / images)</label>
                                            <input type="file" x-ref="evidenceInput" multiple accept="image/*"
                                                @change="onFilesChanged($event)"
                                                class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-gray-200 file:text-xs file:font-medium file:text-gray-600 file:bg-gray-50 hover:file:bg-gray-100 cursor-pointer">
                                        </div>

                                        {{-- Image previews --}}
                                        <template x-if="previewUrls.length > 0">
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="(url, idx) in previewUrls" :key="idx">
                                                    <a :href="url" target="_blank">
                                                        <img :src="url" class="h-20 w-20 object-cover rounded-lg border border-gray-200 hover:opacity-90 transition">
                                                    </a>
                                                </template>
                                            </div>
                                        </template>

                                        <div class="flex items-center gap-2 pt-1">
                                            <button @click="submitFinding()" :disabled="!findingDraft.trim() || findingSubmitting"
                                                class="text-xs px-4 py-1.5 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 disabled:opacity-40 transition flex items-center gap-1.5">
                                                <svg x-show="findingSubmitting" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                                </svg>
                                                <span x-text="findingSubmitting ? 'Saving…' : 'Save Finding'"></span>
                                            </button>
                                            <button @click="showFindingForm = false"
                                                class="text-xs px-3 py-1.5 border border-gray-200 text-gray-500 rounded-lg hover:bg-gray-50 transition">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Saved finding display --}}
                                <template x-if="!showFindingForm && validationStatus === 'not_passed' && finding">
                                    <div class="border border-red-100 rounded-xl bg-red-50 p-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Finding</p>
                                            <button @click="openFindingForm()" class="text-xs text-gray-400 hover:text-gray-600 underline">Edit</button>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="finding"></p>
                                        <template x-if="evidenceImages.length > 0">
                                            <div class="flex flex-wrap gap-2 pt-1">
                                                <template x-for="(url, idx) in evidenceImages" :key="idx">
                                                    <a :href="url" target="_blank">
                                                        <img :src="url" class="h-20 w-20 object-cover rounded-lg border border-red-200 hover:opacity-90 transition">
                                                    </a>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                            </td>
                        </tr>

                    </tbody>
                    @empty
                    <tbody>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">
                                No modules in this suite.
                                <a href="{{ route('product-test-suites.edit', $productTestSuite) }}" class="text-brand-teal hover:underline ml-1">Add modules</a>
                            </td>
                        </tr>
                    </tbody>
                    @endforelse
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
