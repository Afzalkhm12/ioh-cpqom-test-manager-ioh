<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight">
            {{ __('Salesforce Test Manager - Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-bold text-brand-dark tracking-wide">Communications Cloud Modules</h3>
                @if(auth()->user()->role === 'Admin')
                <div class="space-x-3">
                    <a href="{{ route('sf-users.index') }}" class="px-4 py-2 bg-brand-dark hover:bg-opacity-90 text-white rounded-lg shadow transition-colors font-medium text-sm">
                        Manage Personas
                    </a>
                    <a href="{{ route('testers.index') }}" class="px-4 py-2 bg-brand-purple hover:bg-opacity-90 text-white rounded-lg shadow transition-colors font-medium text-sm">
                        Manage Team
                    </a>
                </div>
                @endif
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border-l-4 border-brand-teal p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Total Tests Executed</div>
                    <div class="text-3xl font-bold text-brand-dark">0</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border-l-4 border-brand-pink p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">UI Pass Rate</div>
                    <div class="text-3xl font-bold text-brand-dark">--%</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border-l-4 border-brand-yellow p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">API Pass Rate</div>
                    <div class="text-3xl font-bold text-brand-dark">--%</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border-l-4 border-brand-red p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Apex Pass Rate</div>
                    <div class="text-3xl font-bold text-brand-dark">--%</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($modules as $module)
                    <div class="bg-white shadow-md sm:rounded-2xl p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xl font-bold text-brand-dark group-hover:text-brand-teal transition-colors">
                                {{ $module->name }}
                            </h4>
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-brand-purple bg-brand-purple bg-opacity-10 rounded-full">
                                {{ $module->test_cases_count }} Tests
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-6 line-clamp-2">
                            {{ $module->description ?? 'No description provided.' }}
                        </p>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('modules.show', $module) }}" class="inline-flex items-center justify-center flex-1 px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-brand-dark hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-dark transition-colors">
                                View Module
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No modules</h3>
                        <p class="mt-1 text-sm text-gray-500">Wait for the admin to seed the modules, or configure the database.</p>
                    </div>
                @endforelse
            </div>
            
        </div>
    </div>
</x-app-layout>
