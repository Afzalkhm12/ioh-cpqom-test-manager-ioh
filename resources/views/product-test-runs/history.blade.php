<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-brand-dark leading-tight">
                    Run History — {{ $testModule->display_name }}
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $productTestSuite->name }}</p>
            </div>
            <a href="{{ route('product-test-suites.show', $productTestSuite) }}"
                class="text-xs px-3 py-1.5 border border-gray-200 text-gray-400 rounded-lg hover:bg-gray-50 transition">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-brand-dark">All Runs</h3>
                    <span class="text-xs text-gray-400">{{ $runs->total() }} total</span>
                </div>

                @if($runs->isEmpty())
                    <div class="px-6 py-12 text-center text-gray-400 text-sm">
                        No runs recorded yet for this module.
                    </div>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3 text-left w-20">Run ID</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-left">Validation</th>
                                <th class="px-5 py-3 text-left">Jira</th>
                                <th class="px-5 py-3 text-left">Started</th>
                                <th class="px-5 py-3 text-left">Duration</th>
                                <th class="px-5 py-3 text-left">Log</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($runs as $run)
                            @php
                                $statusColor = match($run->status) {
                                    'success'  => 'bg-green-50 text-green-700 border border-green-300',
                                    'error'    => 'bg-red-500 text-white',
                                    'aborted'  => 'bg-yellow-500 text-white',
                                    'running'  => 'bg-blue-50 text-blue-700 border border-blue-300',
                                    default    => 'bg-gray-100 text-gray-600 border border-gray-300',
                                };
                                $validationColor = match($run->validation_status) {
                                    'passed'     => 'bg-green-500 text-white',
                                    'not_passed' => 'bg-red-500 text-white',
                                    default      => null,
                                };
                                $duration = $run->started_at && $run->finished_at
                                    ? $run->started_at->diffForHumans($run->finished_at, true)
                                    : null;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 font-mono text-xs text-gray-500">#{{ $run->id }}</td>

                                {{-- Status --}}
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-semibold capitalize {{ $statusColor }}">
                                        {{ $run->status }}
                                    </span>
                                </td>

                                {{-- Validation --}}
                                <td class="px-5 py-3">
                                    @if($validationColor)
                                        <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $validationColor }}">
                                            {{ $run->validation_status === 'passed' ? 'Passed ✓' : 'Not Passed ✗' }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Jira ticket --}}
                                <td class="px-5 py-3">
                                    @if($run->jira_ticket)
                                        <a href="{{ $jiraUrl }}/{{ $run->jira_ticket }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition">
                                            {{ $run->jira_ticket }}
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Started at --}}
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    @if($run->started_at)
                                        <span title="{{ $run->started_at->format('Y-m-d H:i:s') }}">
                                            {{ $run->started_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>

                                {{-- Duration --}}
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ $duration ?? '—' }}
                                </td>

                                {{-- Log preview --}}
                                <td class="px-5 py-3 max-w-xs">
                                    @if($run->log)
                                        <details class="group">
                                            <summary class="text-xs text-red-600 cursor-pointer hover:underline select-none">
                                                View log
                                            </summary>
                                            <pre class="mt-2 text-xs text-red-700 bg-red-50 border border-red-200 rounded p-2 whitespace-pre-wrap break-all">{{ $run->log }}</pre>
                                        </details>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($runs->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $runs->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
