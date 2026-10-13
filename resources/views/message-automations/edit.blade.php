<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Editar automatizacion</h2></x-slot>
    <div class="py-8"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-6 shadow-sm sm:rounded-lg">
        <form method="POST" action="{{ route('message-automations.update', $messageAutomation) }}">@method('PUT') @include('message-automations._form')</form>
        <form method="POST" action="{{ route('message-automations.destroy', $messageAutomation) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-600" onclick="return confirm('Eliminar esta automatizacion?')">Eliminar</button></form>
    </div></div></div>
</x-app-layout>
