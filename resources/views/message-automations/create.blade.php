<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Nueva automatizacion</h2></x-slot>
    <div class="py-8"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-6 shadow-sm sm:rounded-lg">
        <form method="POST" action="{{ route('message-automations.store') }}">@include('message-automations._form')</form>
    </div></div></div>
</x-app-layout>
