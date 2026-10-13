<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Evolution API</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('shared.status')
        <div class="mb-4 flex justify-end"><a href="{{ route('evolution-settings.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white">Nueva configuracion</a></div>
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="p-3">Nombre</th><th class="p-3">URL</th><th class="p-3">Instancia</th><th class="p-3">Estado</th><th class="p-3"></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($settings as $setting)
                        <tr><td class="p-3">{{ $setting->name }}</td><td class="p-3">{{ $setting->base_url }}</td><td class="p-3">{{ $setting->instance_name }}</td><td class="p-3">{{ $setting->is_active ? 'Activa' : 'Inactiva' }}</td><td class="p-3 text-right"><a class="text-indigo-600" href="{{ route('evolution-settings.edit', $setting) }}">Editar</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-gray-500">Aun no hay configuraciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $settings->links() }}</div>
    </div></div>
</x-app-layout>
