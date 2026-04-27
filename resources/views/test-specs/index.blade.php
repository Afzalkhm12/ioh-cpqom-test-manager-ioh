<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight">
            {{ __('Spec Files') }}
        </h2>
    </x-slot>

    <style>
        .spec-modal {
            display: none;
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            z-index: 9999 !important;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
        }
        .spec-modal.active { display: flex !important; }
        .spec-modal-box {
            position: relative;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
            width: 100%;
            max-width: 32rem;
            padding: 1.5rem;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-brand-dark">Spec Files Registry</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Define the spec files that can be assigned to test modules and triggered via the automation runner.</p>
                    </div>
                    <button onclick="openModal('add-spec-modal')"
                        class="text-xs px-3 py-1.5 bg-brand-teal text-white rounded-lg font-semibold hover:opacity-90 transition shrink-0">
                        + Add Spec
                    </button>
                </div>

                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Display Name</th>
                            <th class="px-6 py-3 text-left">Runner Key</th>
                            <th class="px-6 py-3 text-left">File Path</th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-center">Used By</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($specs as $spec)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-semibold text-brand-dark">{{ $spec->display_name }}</td>
                            <td class="px-6 py-3">
                                <code class="text-xs bg-gray-100 px-2 py-0.5 rounded font-mono text-gray-700">{{ $spec->runner_key }}</code>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $spec->file_path }}</td>
                            <td class="px-6 py-3 text-xs text-gray-500">{{ $spec->description ?? '—' }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="text-xs font-semibold {{ $spec->test_modules_count > 0 ? 'text-brand-teal' : 'text-gray-300' }}">
                                    {{ $spec->test_modules_count }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <button onclick="openEditModal({{ $spec->toJson() }})"
                                        class="text-xs text-brand-teal hover:underline font-medium">Edit</button>
                                    <form method="POST" action="{{ route('test-specs.destroy', $spec) }}"
                                        onsubmit="return confirm('Delete \'{{ addslashes($spec->display_name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500 hover:underline font-medium">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No spec files defined yet. Click "+ Add Spec" to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Add Modal --}}
    <div id="add-spec-modal" class="spec-modal" onclick="if(event.target===this) closeModal('add-spec-modal')">
        <div class="spec-modal-box">
            <h3 class="font-bold text-lg mb-4 text-brand-dark">Add Spec File</h3>
            <form method="POST" action="{{ route('test-specs.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="add_display_name" value="Display Name" />
                    <x-text-input id="add_display_name" name="display_name" type="text" class="mt-1 block w-full"
                        placeholder="e.g. Account Management" required />
                </div>
                <div>
                    <x-input-label for="add_runner_key" value="Runner Key" />
                    <x-text-input id="add_runner_key" name="runner_key" type="text" class="mt-1 block w-full font-mono"
                        placeholder="e.g. account_mgmt" required />
                    <p class="mt-1 text-xs text-gray-400">Sent to the runner as <code class="bg-gray-100 px-1 rounded">{"modules": ["runner_key"]}</code></p>
                </div>
                <div>
                    <x-input-label for="add_file_path" value="File Path" />
                    <x-text-input id="add_file_path" name="file_path" type="text" class="mt-1 block w-full font-mono"
                        placeholder="e.g. tests/non-ida/01-account-mgmt.spec.js" required />
                </div>
                <div>
                    <x-input-label for="add_description" value="Description (optional)" />
                    <x-text-input id="add_description" name="description" type="text" class="mt-1 block w-full" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('add-spec-modal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-brand-teal text-white rounded-lg text-sm font-semibold hover:opacity-90">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="edit-spec-modal" class="spec-modal" onclick="if(event.target===this) closeModal('edit-spec-modal')">
        <div class="spec-modal-box">
            <h3 class="font-bold text-lg mb-4 text-brand-dark">Edit Spec File</h3>
            <form id="edit-spec-form" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div>
                    <x-input-label for="edit_display_name" value="Display Name" />
                    <x-text-input id="edit_display_name" name="display_name" type="text" class="mt-1 block w-full" required />
                </div>
                <div>
                    <x-input-label for="edit_runner_key" value="Runner Key" />
                    <x-text-input id="edit_runner_key" name="runner_key" type="text" class="mt-1 block w-full font-mono" required />
                    <p class="mt-1 text-xs text-gray-400">Sent to the runner as <code class="bg-gray-100 px-1 rounded">{"modules": ["runner_key"]}</code></p>
                </div>
                <div>
                    <x-input-label for="edit_file_path" value="File Path" />
                    <x-text-input id="edit_file_path" name="file_path" type="text" class="mt-1 block w-full font-mono" required />
                </div>
                <div>
                    <x-input-label for="edit_description" value="Description (optional)" />
                    <x-text-input id="edit_description" name="description" type="text" class="mt-1 block w-full" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('edit-spec-modal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-brand-teal text-white rounded-lg text-sm font-semibold hover:opacity-90">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        function openEditModal(spec) {
            document.getElementById('edit_display_name').value = spec.display_name ?? '';
            document.getElementById('edit_runner_key').value   = spec.runner_key ?? '';
            document.getElementById('edit_file_path').value    = spec.file_path ?? '';
            document.getElementById('edit_description').value  = spec.description ?? '';
            document.getElementById('edit-spec-form').action   = `/test-specs/${spec.id}`;
            openModal('edit-spec-modal');
        }
    </script>

</x-app-layout>
