@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $messageTemplate->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="slug" value="Codigo" />
        <x-text-input id="slug" name="slug" class="mt-1 block w-full" :value="old('slug', $messageTemplate->slug)" />
    </div>
</div>
<div class="mt-4">
    <x-input-label for="body" value="Mensaje" />
    <textarea id="body" name="body" rows="7" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('body', $messageTemplate->body) }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Puedes usar variables como @{{ nombre }}, @{{ telefono }} y @{{ email }}.</p>
    <x-input-error :messages="$errors->get('body')" class="mt-2" />
</div>
<div class="mt-4">
    <x-input-label for="description" value="Descripcion" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $messageTemplate->description) }}</textarea>
</div>
<label class="mt-4 flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $messageTemplate->is_active ?? true))>
    <span class="text-sm text-gray-700">Activa</span>
</label>
<div class="mt-6 flex gap-3">
    <x-primary-button>Guardar</x-primary-button>
    <a href="{{ route('message-templates.index') }}" class="rounded-md border px-4 py-2 text-sm">Cancelar</a>
</div>
