<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-brand-dark transition-colors">Dashboard</a>
            <span class="text-gray-300">/</span>
            {{ $module->name }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h3 class="text-2xl font-bold text-brand-dark">{{ $module->name }} Tests</h3>
                    <p class="text-gray-600 mt-1">{{ $module->description }}</p>
                    @if($module->testModules->isNotEmpty())
                        <div class="mt-2 flex items-center flex-wrap gap-2">
                            <span class="text-xs text-gray-500">Linked suites:</span>
                            @foreach($module->testModules as $tm)
                                <a href="{{ route('test-suite.show', $tm) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-brand-teal bg-opacity-10 text-brand-teal hover:bg-opacity-20 transition-colors">
                                    {{ $tm->display_name }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <button class="px-4 py-2 bg-brand-teal hover:bg-opacity-90 text-white rounded-lg shadow font-medium text-sm transition-colors cursor-pointer"
                            onclick="document.getElementById('link-suite-modal').classList.remove('hidden')">
                        {{ $module->testModules->isNotEmpty() ? '⇄ Edit Linked Suites' : '+ Link Test Suites' }}
                    </button>
                    <!-- Mock pull allure report button to simulate external CI pulling -->
                    <form action="{{ route('test-runs.store') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="action_type" value="pull_allure">
                        <input type="hidden" name="module_id" value="{{ $module->id }}">
                        <button type="submit" class="px-4 py-2 bg-white border border-brand-dark text-brand-dark hover:bg-gray-50 rounded-lg shadow-sm font-medium text-sm transition-colors">
                            Pull Allure UI Reports
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 text-left">Suite Name</th>
                                <th class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 text-left">Key</th>
                                <th class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 text-left">Spec</th>
                                <th class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 text-left">Counter</th>
                                <th class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($module->testModules as $tm)
                            <tr class="hover:bg-gray-50 transition-colors"
                                x-data="{
                                    running: false,
                                    result: null,
                                    async run() {
                                        this.running = true;
                                        this.result = null;
                                        try {
                                            const res = await fetch('{{ route('test-suite.run', $tm) }}', {
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
                                }">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                    <a href="{{ route('test-suite.show', $tm) }}"
                                       class="hover:text-brand-teal transition-colors">
                                        {{ $tm->display_name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-500 font-mono text-xs">
                                    {{ $tm->module_key }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                                    @if($tm->spec)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-700">
                                            {{ $tm->spec->runner_key }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">No spec</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                                    {{ $tm->counter }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <!-- Run result badge -->
                                        <template x-if="result">
                                            <span :class="result.error || result.status >= 400 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200'"
                                                  class="text-xs px-2 py-1 rounded border font-medium"
                                                  x-text="result.error ? 'Error' : (result.status >= 400 ? 'Failed' : 'OK')">
                                            </span>
                                        </template>

                                        @if($tm->spec)
                                            <button @click="run()" :disabled="running"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-teal text-white text-xs font-medium rounded-lg shadow hover:opacity-90 transition disabled:opacity-50">
                                                <span x-show="!running">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                                <span x-show="running" class="animate-spin text-sm">⟳</span>
                                                <span x-text="running ? 'Running…' : 'Run'"></span>
                                            </button>
                                        @else
                                            <span class="text-gray-400 italic text-xs">No spec assigned</span>
                                        @endif

                                        <a href="{{ route('test-suite.show', $tm) }}"
                                           class="text-xs text-gray-400 hover:text-brand-teal transition-colors">
                                            View →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No test suites linked yet. Use the <strong>Link Test Suites</strong> button above.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Link Test Suite Modal -->
    <div id="link-suite-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                 onclick="document.getElementById('link-suite-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('modules.link-test-suite', $module) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-brand-dark mb-1">Link Test Suites</h3>
                        <p class="text-sm text-gray-500 mb-4">Select one or more test suites to associate with this module. Uncheck all to remove every link.</p>

                        @php $linkedIds = $module->testModules->pluck('id')->all(); @endphp

                        <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                            @forelse($testModules as $tm)
                                <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox"
                                           name="test_module_ids[]"
                                           value="{{ $tm->id }}"
                                           {{ in_array($tm->id, $linkedIds) ? 'checked' : '' }}
                                           class="mt-0.5 rounded border-gray-300 text-brand-teal focus:ring-brand-teal">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $tm->display_name }}</div>
                                        @if($tm->description)
                                            <div class="text-xs text-gray-500">{{ Str::limit($tm->description, 70) }}</div>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-gray-400 py-2 text-center">No test suites available.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100">
                        <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-brand-teal text-sm font-medium text-white hover:bg-opacity-90">
                            Save
                        </button>
                        <button type="button"
                                onclick="document.getElementById('link-suite-modal').classList.add('hidden')"
                                class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
