<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Global stat tiles --}}
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand-teal/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-brand-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $totalSuites }}</p>
                        <p class="text-xs text-gray-400 font-medium">Product Suites</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $globalPassed }}</p>
                        <p class="text-xs text-gray-400 font-medium">Modules Passed</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $globalNotPassed }}</p>
                        <p class="text-xs text-gray-400 font-medium">Not Passed</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand-teal/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-brand-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-dark tabular-nums">{{ $fullyPassedSuites }}</p>
                        <p class="text-xs text-gray-400 font-medium">Products Fully Passed</p>
                    </div>
                </div>
            </div>

            {{-- Main content: suite list + recent runs --}}
            <div class="flex gap-6 items-start">

                {{-- Suite list grouped by product line --}}
                <div class="flex-1 min-w-0 space-y-5">
                    @forelse($suitesByLine as $productLine => $linesuites)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ open: false }">
                        {{-- Accordion header --}}
                        <button @click="open = !open"
                            class="w-full px-5 py-3 bg-gray-50 flex items-center justify-between hover:bg-gray-100 transition">
                            <div class="flex items-center gap-3">
                                <svg :class="open ? 'rotate-90' : ''" class="w-3.5 h-3.5 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">{{ $productLine }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $linesuites->count() }} {{ Str::plural('suite', $linesuites->count()) }}</span>
                        </button>

                        <table x-show="open" x-transition class="min-w-full text-sm border-t border-gray-100">
                            <tbody class="divide-y divide-gray-50">
                            @foreach($linesuites as $suite)
                            @php
                                $total   = $suite->modules_count;
                                $passed  = $suite->stat_passed;
                                $failed  = $suite->stat_not_passed;
                                $error   = $suite->stat_error;
                                $notRun  = $suite->stat_not_run;

                                // Overall suite status label
                                if ($total === 0) {
                                    $suiteStatus = ['label' => 'No Modules', 'class' => 'text-gray-400'];
                                } elseif ($notRun === $total) {
                                    $suiteStatus = ['label' => 'Not Started', 'class' => 'text-gray-400'];
                                } elseif ($passed === $total) {
                                    $suiteStatus = ['label' => 'All Passed', 'class' => 'text-green-600'];
                                } elseif ($failed > 0) {
                                    $suiteStatus = ['label' => 'Has Failures', 'class' => 'text-red-500'];
                                } elseif ($error > 0) {
                                    $suiteStatus = ['label' => 'Has Errors', 'class' => 'text-orange-500'];
                                } else {
                                    $suiteStatus = ['label' => 'In Progress', 'class' => 'text-brand-teal'];
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 w-28">
                                    <span class="text-xs font-mono px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">
                                        {{ $suite->product->product_code }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-medium text-brand-dark text-sm leading-tight">{{ $suite->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $suite->product->product_offer }}</p>
                                </td>
                                <td class="px-3 py-3 w-48">
                                    @if($total > 0)
                                    <div class="space-y-1">
                                        <div class="flex h-1.5 rounded-full overflow-hidden bg-gray-100 gap-px">
                                            @if($passed > 0)
                                            <div class="bg-green-400" style="width:{{ round($passed/$total*100) }}%"></div>
                                            @endif
                                            @if($failed > 0)
                                            <div class="bg-red-400" style="width:{{ round($failed/$total*100) }}%"></div>
                                            @endif
                                            @if($error > 0)
                                            <div class="bg-orange-300" style="width:{{ round($error/$total*100) }}%"></div>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-400 tabular-nums">
                                            <span class="font-semibold text-brand-dark">{{ $passed }}</span> / {{ $total }} passed
                                        </p>
                                    </div>
                                    @else
                                    <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 w-28">
                                    <span class="text-xs font-semibold {{ $suiteStatus['class'] }}">{{ $suiteStatus['label'] }}</span>
                                </td>
                                <td class="px-3 py-3 w-28 text-xs text-gray-400 tabular-nums">
                                    @if($suite->last_run_at)
                                        {{ $suite->last_run_at->diffForHumans() }}
                                    @else
                                        <span class="text-gray-300">Never</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right w-16">
                                    <a href="{{ route('product-test-suites.show', $suite) }}"
                                        class="text-xs text-brand-teal hover:underline font-medium">View</a>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @empty
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-12 text-center">
                        <p class="text-gray-400 text-sm">No product test suites yet.</p>
                        <a href="{{ route('product-test-suites.create') }}" class="text-brand-teal text-sm hover:underline mt-1 inline-block">Create one</a>
                    </div>
                    @endforelse
                </div>

                {{-- Recent runs sidebar --}}
                <div class="w-72 shrink-0 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-brand-dark">Recent Runs</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @forelse($recentRuns as $run)
                        @php
                            $badge = match(true) {
                                $run->validation_status === 'passed'     => ['Passed',     'bg-green-100 text-green-700'],
                                $run->validation_status === 'not_passed' => ['Not Passed', 'bg-red-100 text-red-600'],
                                $run->status === 'error'                 => ['Error',      'bg-red-100 text-red-600'],
                                $run->status === 'aborted'               => ['Aborted',    'bg-yellow-100 text-yellow-700'],
                                $run->status === 'success'               => ['Success',    'bg-green-50 text-green-600'],
                                $run->status === 'running'               => ['Running',    'bg-blue-100 text-blue-600'],
                                default                                  => ['—',          'bg-gray-100 text-gray-500'],
                            };
                        @endphp
                        <div class="px-5 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-brand-dark truncate">{{ $run->module?->display_name }}</p>
                                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $run->suite?->product?->product_code }}</p>
                                </div>
                                <span class="shrink-0 text-xs px-1.5 py-0.5 rounded-full font-semibold {{ $badge[1] }}">
                                    {{ $badge[0] }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-300 mt-1">{{ $run->started_at?->diffForHumans() }}</p>
                        </div>
                        @empty
                        <div class="px-5 py-8 text-center text-xs text-gray-300">No runs yet</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
