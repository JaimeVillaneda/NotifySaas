<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Plantillas</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('shared.status')
        <div class="mb-4 flex justify-end"><a href="{{ route('message-templates.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white">Nueva plantilla</a></div>
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="p-3">Nombre</th><th class="p-3">Codigo</th><th class="p-3">Estado</th><th class="p-3"></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($templates as $template)
                        <tr><td class="p-3">{{ $template->name }}</td><td class="p-3">{{ $template->slug }}</td><td class="p-3">{{ $template->is_active ? 'Activa' : 'Inactiva' }}</td><td class="p-3 text-right"><a class="text-indigo-600" href="{{ route('message-templates.edit', $template) }}">Editar</a></td></tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-gray-500">Aun no hay plantillas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $templates->links() }}</div>
    </div></div>
</x-app-layout>
