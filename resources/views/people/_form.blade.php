@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="first_name" value="Nombre" />
        <x-text-input id="first_name" name="first_name" class="mt-1 block w-full" :value="old('first_name', $person->first_name)" required />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="last_name" value="Apellido" />
        <x-text-input id="last_name" name="last_name" class="mt-1 block w-full" :value="old('last_name', $person->last_name)" />
    </div>
    <div>
        <x-input-label for="phone" value="Telefono WhatsApp" />
        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $person->phone)" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $person->email)" />
    </div>
    <div>
        <x-input-label for="document" value="Documento" />
        <x-text-input id="document" name="document" class="mt-1 block w-full" :value="old('document', $person->document)" />
    </div>
    <label class="flex items-center gap-2 pt-6">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $person->is_active ?? true))>
        <span class="text-sm text-gray-700">Activo</span>
    </label>
</div>
<div class="mt-4">
    <x-input-label for="notes" value="Notas" />
    <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $person->notes) }}</textarea>
</div>
<div class="mt-6 flex gap-3">
    <x-primary-button>Guardar</x-primary-button>
    <a href="{{ route('people.index') }}" class="rounded-md border px-4 py-2 text-sm">Cancelar</a>
</div>
