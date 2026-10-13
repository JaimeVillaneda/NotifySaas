<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Personas</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('shared.status')
        <div class="mb-4 flex justify-end"><a href="{{ route('people.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white">Nueva persona</a></div>
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="p-3">Nombre</th><th class="p-3">Telefono</th><th class="p-3">Estado</th><th class="p-3"></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($people as $person)
                        <tr><td class="p-3">{{ $person->full_name }}</td><td class="p-3">{{ $person->phone }}</td><td class="p-3">{{ $person->is_active ? 'Activo' : 'Inactivo' }}</td><td class="p-3 text-right"><a class="text-indigo-600" href="{{ route('people.edit', $person) }}">Editar</a></td></tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-gray-500">Aun no hay personas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $people->links() }}</div>
    </div></div>
</x-app-layout>
