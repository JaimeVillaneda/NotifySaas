@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $evolutionSetting->name ?? 'Principal')" required />
    </div>
    <div>
        <x-input-label for="instance_name" value="Instancia" />
        <x-text-input id="instance_name" name="instance_name" class="mt-1 block w-full" :value="old('instance_name', $evolutionSetting->instance_name)" />
    </div>
    <div>
        <x-input-label for="base_url" value="URL base" />
        <x-text-input id="base_url" name="base_url" class="mt-1 block w-full" :value="old('base_url', $evolutionSetting->base_url)" placeholder="https://tu-evolution-api" />
    </div>
    <div>
        <x-input-label for="webhook_url" value="Webhook" />
        <x-text-input id="webhook_url" name="webhook_url" class="mt-1 block w-full" :value="old('webhook_url', $evolutionSetting->webhook_url)" />
    </div>
</div>
<div class="mt-4">
    <x-input-label for="api_key" value="API key" />
    <x-text-input id="api_key" name="api_key" type="password" class="mt-1 block w-full" />
    <p class="mt-1 text-xs text-gray-500">En edicion, dejalo vacio para conservar la key actual.</p>
</div>
<label class="mt-4 flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $evolutionSetting->is_active ?? true))>
    <span class="text-sm text-gray-700">Activa</span>
</label>
<div class="mt-6 flex gap-3">
    <x-primary-button>Guardar</x-primary-button>
    <a href="{{ route('evolution-settings.index') }}" class="rounded-md border px-4 py-2 text-sm">Cancelar</a>
</div>
