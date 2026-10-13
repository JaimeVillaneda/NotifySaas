<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Editar persona</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-6 shadow-sm sm:rounded-lg">
        <form method="POST" action="{{ route('people.update', $person) }}">@method('PUT') @include('people._form')</form>
        <form method="POST" action="{{ route('people.destroy', $person) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-600" onclick="return confirm('Eliminar esta persona?')">Eliminar</button></form>
    </div></div></div>
</x-app-layout>
