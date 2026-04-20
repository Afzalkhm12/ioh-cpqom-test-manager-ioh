<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight flex justify-between items-center">
            {{ __('Object Sync Manager') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8">
            
            <!-- Sync Form -->
            <div class="w-full md:w-1/3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-brand-dark mb-4">Sync New Object</h3>
                    @if(session('success'))
                        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
                            {{ session('success') }}
                        </div>
                    @endif
                    <form action="{{ route('object-sync.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="api_name" value="Object API Name" />
                            <x-text-input id="api_name" name="api_name" type="text" class="mt-1 block w-full" placeholder="e.g. Account or Custom__c" required />
                            <p class="text-xs text-gray-400 mt-1">Make sure the API Name is exact.</p>
                            
                            @if($errors->has('api_name'))
                                <div class="mt-2 text-sm text-red-600 font-medium">
                                    {{ $errors->first('api_name') }}
                                </div>
                            @endif
                        </div>
                        <div class="pt-2">
                            <x-primary-button class="bg-brand-teal hover:bg-opacity-90 w-full justify-center">
                                Sync from Salesforce
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Views -->
            <div class="w-full md:w-2/3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-brand-dark mb-4">Synced Objects Dictionary</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Label</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">API Name</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Fields</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($objects as $obj)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-brand-dark">
                                        <a href="{{ route('object-sync.show', $obj) }}" class="text-brand-purple hover:underline">{{ $obj->label ?? $obj->api_name }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700 font-mono">{{ $obj->api_name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $obj->fields_count }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <form action="{{ route('object-sync.destroy', $obj) }}" method="POST" class="inline" onsubmit="return confirm('Delete this object dictionary?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">No objects synced yet. Sync one to begin building your dictionary.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
