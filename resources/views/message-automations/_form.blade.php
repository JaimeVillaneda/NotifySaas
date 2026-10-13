@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $messageAutomation->name)" required />
    </div>
    <div>
        <x-input-label for="message_template_id" value="Plantilla" />
        <select id="message_template_id" name="message_template_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="">Seleccionar</option>
            @foreach ($templates as $template)
                <option value="{{ $template->id }}" @selected(old('message_template_id', $messageAutomation->message_template_id) == $template->id)>{{ $template->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="frequency" value="Frecuencia" />
        <select id="frequency" name="frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @foreach (['once' => 'Una vez', 'daily' => 'Diario', 'weekly' => 'Semanal', 'interval' => 'Cada intervalo'] as $value => $label)
                <option value="{{ $value }}" @selected(old('frequency', $messageAutomation->frequency) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="send_time" value="Hora de envio" />
        <x-text-input id="send_time" name="send_time" type="time" class="mt-1 block w-full" :value="old('send_time', $messageAutomation->send_time)" />
    </div>
    <div>
        <x-input-label for="start_date" value="Fecha inicio" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', optional($messageAutomation->start_date)->format('Y-m-d'))" />
    </div>
    <div>
        <x-input-label for="end_date" value="Fecha fin" />
        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', optional($messageAutomation->end_date)->format('Y-m-d'))" />
    </div>
    <div>
        <x-input-label for="interval_value" value="Enviar cada" />
        <x-text-input id="interval_value" name="interval_value" type="number" min="1" class="mt-1 block w-full" :value="old('interval_value', $messageAutomation->interval_value)" />
    </div>
    <div>
        <x-input-label for="interval_unit" value="Unidad" />
        <select id="interval_unit" name="interval_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">Seleccionar</option>
            @foreach (['minutes' => 'Minutos', 'hours' => 'Horas', 'days' => 'Dias'] as $value => $label)
                <option value="{{ $value }}" @selected(old('interval_unit', $messageAutomation->interval_unit) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-4">
    <x-input-label value="Dias de la semana" />
    <div class="mt-2 grid gap-2 sm:grid-cols-4">
        @foreach ([1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'] as $day => $label)
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="week_days[]" value="{{ $day }}" @checked(in_array($day, old('week_days', $messageAutomation->week_days ?? [])))> {{ $label }}</label>
        @endforeach
    </div>
</div>
<div class="mt-4">
    <x-input-label for="notes" value="Notas" />
    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $messageAutomation->notes) }}</textarea>
</div>
<label class="mt-4 flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $messageAutomation->is_active ?? true))>
    <span class="text-sm text-gray-700">Activa</span>
</label>
<div class="mt-6 flex gap-3">
    <x-primary-button>Guardar</x-primary-button>
    <a href="{{ route('message-automations.index') }}" class="rounded-md border px-4 py-2 text-sm">Cancelar</a>
</div>
