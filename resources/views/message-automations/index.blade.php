<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Automatizaciones</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('shared.status')
        <div class="mb-4 flex justify-end"><a href="{{ route('message-automations.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white">Nueva automatizacion</a></div>
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="p-3">Nombre</th><th class="p-3">Plantilla</th><th class="p-3">Frecuencia</th><th class="p-3">Hora</th><th class="p-3">Estado</th><th class="p-3"></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($automations as $automation)
                        <tr><td class="p-3">{{ $automation->name }}</td><td class="p-3">{{ $automation->template?->name }}</td><td class="p-3">{{ $automation->frequency }}</td><td class="p-3">{{ $automation->send_time }}</td><td class="p-3">{{ $automation->is_active ? 'Activa' : 'Inactiva' }}</td><td class="p-3 text-right"><a class="text-indigo-600" href="{{ route('message-automations.edit', $automation) }}">Editar</a></td></tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">Aun no hay automatizaciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $automations->links() }}</div>
    </div></div>
</x-app-layout>
