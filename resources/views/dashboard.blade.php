<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid gap-4 md:grid-cols-4">
                        <a href="{{ route('people.index') }}" class="rounded border p-4 hover:bg-gray-50">
                            <div class="text-sm text-gray-500">Contactos</div>
                            <div class="mt-2 text-2xl font-semibold">Personas</div>
                        </a>
                        <a href="{{ route('message-templates.index') }}" class="rounded border p-4 hover:bg-gray-50">
                            <div class="text-sm text-gray-500">Mensajes reutilizables</div>
                            <div class="mt-2 text-2xl font-semibold">Plantillas</div>
                        </a>
                        <a href="{{ route('message-automations.index') }}" class="rounded border p-4 hover:bg-gray-50">
                            <div class="text-sm text-gray-500">Envios programados</div>
                            <div class="mt-2 text-2xl font-semibold">Automatizar</div>
                        </a>
                        <a href="{{ route('evolution-settings.index') }}" class="rounded border p-4 hover:bg-gray-50">
                            <div class="text-sm text-gray-500">Conexion WhatsApp</div>
                            <div class="mt-2 text-2xl font-semibold">Evolution API</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
