<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-dark leading-tight flex justify-between items-center">
            {{ __('Object Dictionary: ') }} {{ $object->label ?? $object->api_name }}
            <a href="{{ route('object-sync.index') }}" class="text-sm px-4 py-2 bg-brand-dark text-white rounded hover:bg-black transition-colors">Back to List</a>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 mb-6">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $object->label }} Metadata</h3>
                        <p class="text-sm text-gray-600 font-mono mt-1">API Name: {{ $object->api_name }}</p>
                    </div>
                    <div class="flex gap-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $object->is_creatable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">Createable</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $object->is_updatable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">Updateable</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $object->is_deletable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">Deletable</span>
                    </div>
                </div>

                <div class="p-0">
                    <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-900 text-left">Field Label</th>
                                <th class="px-6 py-3 font-medium text-gray-900 text-left">API Name</th>
                                <th class="px-6 py-3 font-medium text-gray-900 text-left">Type</th>
                                <th class="px-6 py-3 font-medium text-gray-900 text-center">Insertable</th>
                                <th class="px-6 py-3 font-medium text-gray-900 text-center">Updatable</th>
                                <th class="px-6 py-3 font-medium text-gray-900 text-center">Readable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($object->fields as $field)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-900 font-medium">{{ $field->label }}</td>
                                <td class="px-6 py-3 text-gray-700 font-mono">{{ $field->api_name }}</td>
                                <td class="px-6 py-3 text-brand-purple">
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">{{ $field->type }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    {!! $field->is_insertable ? '<span class="text-green-500 font-bold">✓</span>' : '<span class="text-gray-300">-</span>' !!}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    {!! $field->is_updatable ? '<span class="text-green-500 font-bold">✓</span>' : '<span class="text-gray-300">-</span>' !!}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    {!! $field->is_readable ? '<span class="text-green-500 font-bold">✓</span>' : '<span class="text-gray-300">-</span>' !!}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
