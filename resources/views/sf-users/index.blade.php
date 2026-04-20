<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight flex justify-between items-center">
            {{ __('Manage Salesforce Personas') }}
            <a href="{{ route('dashboard') }}" class="text-sm px-4 py-2 bg-brand-dark text-white rounded hover:bg-black transition-colors">Back to Dashboard</a>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-1/3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-brand-dark mb-4">Register Persona</h3>
                    @if(session('success'))
                        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->has('oauth'))
                        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                            {{ $errors->first('oauth') }}
                        </div>
                    @endif
                    <form action="{{ route('sf-users.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="label" value="Role Label (e.g. Sales Rep)" />
                            <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('label')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="username" value="SF Username" />
                            <x-text-input id="username" name="username" type="email" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>
                        <div class="pt-2">
                            <x-primary-button class="bg-brand-purple hover:bg-opacity-90 w-full justify-center">
                                Add Persona
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="w-full md:w-2/3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-brand-dark mb-4">Registered Personas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Label</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Username</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-left">Status</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($sfUsers as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-brand-dark">{{ $user->label }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $user->username }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        @if($user->refresh_token)
                                            <span class="text-green-600 font-bold">Linked</span>
                                        @else
                                            <a href="{{ route('salesforce.redirect', $user->id) }}" class="text-brand-teal hover:text-brand-dark font-medium underline">Authorize in SF</a>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <form action="{{ route('sf-users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this persona?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">No Personas registered yet.</td>
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
