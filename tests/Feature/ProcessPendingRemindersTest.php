
<?php

use App\Models\Company;
use App\Models\MessageAutomation;
use App\Models\MessageDispatch;
use App\Models\MessageTemplate;
use App\Models\Person;
use Illuminate\Support\Facades\Artisan;

it('genera envios solo para clientes activos de la empresa', function () {

    // PASO 1: Crear dos empresas.
    $empresaA = Company::create([
        'name' => 'Empresa Procesamiento A',
        'is_active' => true,
    ]);

    $empresaB = Company::create([
        'name' => 'Empresa Procesamiento B',
        'is_active' => true,
    ]);

    // PASO 2: Crear un cliente activo de Empresa A.
    $clienteA = Person::create([
        'company_id' => $empresaA->id,
        'first_name' => 'Cliente',
        'last_name' => 'Empresa A',
        'phone' => '3001111111',
        'is_active' => true,
    ]);

    // Crear un cliente activo de otra empresa.
    $clienteB = Person::create([
        'company_id' => $empresaB->id,
        'first_name' => 'Cliente',
        'last_name' => 'Empresa B',
        'phone' => '3002222222',
        'is_active' => true,
    ]);

    // Crear un cliente inactivo de Empresa A.
    $clienteInactivo = Person::create([
        'company_id' => $empresaA->id,
        'first_name' => 'Cliente',
        'last_name' => 'Inactivo',
        'phone' => '3003333333',
        'is_active' => false,
    ]);

    // PASO 3: Crear una plantilla de Empresa A.
    $plantilla = MessageTemplate::create([
        'company_id' => $empresaA->id,
        'name' => 'Plantilla Recordatorio A',
        'body' => 'Hola {{ nombre }}, tienes un recordatorio.',
        'is_active' => true,
    ]);

    // PASO 4: Programar un recordatorio de Empresa A.
    $recordatorio = MessageAutomation::create([
        'company_id' => $empresaA->id,
        'message_template_id' => $plantilla->id,
        'name' => 'Recordatorio de prueba',
        'channel' => 'telegram',
        'frequency' => 'once',
        'is_active' => true,
        'status' => 'pending',
        'next_run_at' => now()->subMinute(),
    ]);

    // PASO 5: Ejecutar el comando.
    $resultado = Artisan::call('reminders:process');

    expect($resultado)->toBe(0);

    // PASO 6: Debe generar exactamente un envio.
    expect(MessageDispatch::count())->toBe(1);

    // PASO 7: Comprobar el envio autorizado.
    $this->assertDatabaseHas('message_dispatches', [
        'company_id' => $empresaA->id,
        'person_id' => $clienteA->id,
        'message_automation_id' => $recordatorio->id,
        'status' => 'pending',
        'message' => 'Hola Cliente Empresa A, tienes un recordatorio.',
    ]);

    // PASO 8: No incluir clientes de Empresa B.
    $this->assertDatabaseMissing('message_dispatches', [
        'person_id' => $clienteB->id,
    ]);

    // PASO 9: No incluir clientes inactivos.
    $this->assertDatabaseMissing('message_dispatches', [
        'person_id' => $clienteInactivo->id,
    ]);

    // PASO 10: El recordatorio de una sola
    // ejecucion debe quedar completado.
    $recordatorio->refresh();

    expect($recordatorio->status)->toBe('completed');
});


it('respeta las fechas y horas programadas', function () {

    // Fijar una fecha para ejecutar la prueba.
    $this->travelTo(
        \Illuminate\Support\Carbon::parse(
            '2026-11-05 09:00:00'
        )
    );

    // Crear empresa.
    $empresa = Company::create([
        'name' => 'Empresa Prueba Horarios',
        'is_active' => true,
    ]);

    // Crear cliente activo.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente',
        'last_name' => 'Horarios',
        'phone' => '3008880001',
        'is_active' => true,
    ]);

    // Crear plantilla activa.
    $plantilla = MessageTemplate::create([
        'company_id' => $empresa->id,
        'name' => 'Plantilla Horarios',
        'body' => 'Hola {{ nombre }}, tienes un aviso.',
        'is_active' => true,
    ]);

    // Datos comunes de los recordatorios.
    $datos = [
        'company_id' => $empresa->id,
        'message_template_id' => $plantilla->id,
        'channel' => 'email',
        'frequency' => 'once',
        'is_active' => true,
        'status' => 'pending',
        'next_run_at' => now()->subMinute(),
    ];

    // CASO 1: Empieza mañana.
    $futuro = MessageAutomation::create([
        ...$datos,
        'name' => 'Recordatorio futuro',
        'start_date' => now()->addDay()->toDateString(),
    ]);

    // CASO 2: Venció ayer.
    $vencido = MessageAutomation::create([
        ...$datos,
        'name' => 'Recordatorio vencido',
        'end_date' => now()->subDay()->toDateString(),
    ]);

    // CASO 3: Debe enviarse a las 10:00.
    // La hora simulada actual es 09:00.
    $horaFutura = MessageAutomation::create([
        ...$datos,
        'name' => 'Recordatorio hora futura',
        'send_time' => '10:00:00',
    ]);

    // CASO 4: Debe enviarse a las 08:00.
    // Ya son las 09:00, por tanto es elegible.
    $valido = MessageAutomation::create([
        ...$datos,
        'name' => 'Recordatorio valido',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'send_time' => '08:00:00',
    ]);

    // Ejecutar el procesador.
    $resultado = Artisan::call('reminders:process');

    // El comando debe finalizar correctamente.
    expect($resultado)->toBe(0);

    // Solo debe generarse un envio.
    expect(MessageDispatch::count())->toBe(1);

    // Confirmar el envio del recordatorio valido.
    $this->assertDatabaseHas('message_dispatches', [
        'message_automation_id' => $valido->id,
        'person_id' => $cliente->id,
        'company_id' => $empresa->id,
        'status' => 'pending',
    ]);

    // Confirmar que los otros tres no generaron envios.
    foreach ([$futuro, $vencido, $horaFutura] as $recordatorio) {
        $this->assertDatabaseMissing('message_dispatches', [
            'message_automation_id' => $recordatorio->id,
        ]);
    }

    // Comprobar los estados finales.
    expect($futuro->fresh()->status)->toBe('pending');
    expect($vencido->fresh()->status)->toBe('completed');
    expect($horaFutura->fresh()->status)->toBe('pending');
    expect($valido->fresh()->status)->toBe('completed');
});


it('no duplica envios al ejecutar dos veces el procesador', function () {

    // Fijar la fecha y hora de prueba.
    $this->travelTo(
        \Illuminate\Support\Carbon::parse(
            '2026-11-05 11:00:00'
        )
    );

    // Crear empresa y cliente.
    $empresa = Company::create([
        'name' => 'Empresa Sin Duplicados',
        'is_active' => true,
    ]);

    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente',
        'last_name' => 'Prueba Duplicados',
        'phone' => '3009990001',
        'is_active' => true,
    ]);

    // Crear plantilla.
    $plantilla = MessageTemplate::create([
        'company_id' => $empresa->id,
        'name' => 'Plantilla Sin Duplicados',
        'body' => 'Hola {{ nombre }}',
        'is_active' => true,
    ]);

    // Datos compartidos.
    $datos = [
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'message_template_id' => $plantilla->id,
        'channel' => 'telegram',
        'is_active' => true,
        'status' => 'pending',
        'next_run_at' => now()->subMinute(),
    ];

    // Recordatorio de una sola ejecucion.
    $unico = MessageAutomation::create([
        ...$datos,
        'name' => 'Recordatorio unico',
        'frequency' => 'once',
    ]);

    // Recordatorio que se repite diariamente.
    $diario = MessageAutomation::create([
        ...$datos,
        'name' => 'Recordatorio diario',
        'frequency' => 'daily',
        'send_time' => '10:00:00',
    ]);

    // Primera ejecucion.
    expect(Artisan::call('reminders:process'))->toBe(0);

    // Deben existir dos envios: uno por recordatorio.
    expect(MessageDispatch::count())->toBe(2);

    // Ejecutar nuevamente sin avanzar el tiempo.
    expect(Artisan::call('reminders:process'))->toBe(0);

    // No deben aparecer envios adicionales.
    expect(MessageDispatch::count())->toBe(2);

    // Cada recordatorio genero un unico envio.
    expect(
        MessageDispatch::where(
            'message_automation_id',
            $unico->id
        )->count()
    )->toBe(1);

    expect(
        MessageDispatch::where(
            'message_automation_id',
            $diario->id
        )->count()
    )->toBe(1);

    // El recordatorio unico quedo completado.
    expect($unico->fresh()->status)->toBe('completed');

    // El diario tiene su siguiente ejecucion futura.
    expect(
        $diario->fresh()->next_run_at->gt(now())
    )->toBeTrue();
});


it('impide procesar recordatorios mientras existe un bloqueo', function () {

    // Crear empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa Bloqueo Prueba',
        'is_active' => true,
    ]);

    // Crear un cliente.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente',
        'last_name' => 'Bloqueo',
        'phone' => '3007770001',
        'is_active' => true,
    ]);

    // Crear plantilla.
    $plantilla = MessageTemplate::create([
        'company_id' => $empresa->id,
        'name' => 'Plantilla Bloqueo',
        'body' => 'Hola {{ nombre }}',
        'is_active' => true,
    ]);

    // Crear recordatorio pendiente.
    $recordatorio = MessageAutomation::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'message_template_id' => $plantilla->id,
        'name' => 'Recordatorio Bloqueo',
        'channel' => 'telegram',
        'frequency' => 'once',
        'is_active' => true,
        'status' => 'pending',
        'next_run_at' => now()->subMinute(),
    ]);

    // Simular que otro proceso ya tiene el bloqueo.
    $lock = \Illuminate\Support\Facades\Cache::lock(
        'notifysaas:reminders:process',
        600
    );

    expect($lock->get())->toBeTrue();

    try {
        // Ejecutar el procesador mientras esta bloqueado.
        $resultado = Artisan::call('reminders:process');

        expect($resultado)->toBe(0);

        // No debe generar ningun mensaje.
        expect(MessageDispatch::count())->toBe(0);

        // El recordatorio debe permanecer pendiente.
        expect(
            $recordatorio->fresh()->status
        )->toBe('pending');

    } finally {
        // Liberar el bloqueo.
        $lock->release();
    }

    // Ejecutar nuevamente, ahora sin bloqueo.
    expect(Artisan::call('reminders:process'))->toBe(0);

    // Debe generar exactamente un mensaje.
    expect(MessageDispatch::count())->toBe(1);

    // Comprobar que pertenece al recordatorio.
    $this->assertDatabaseHas('message_dispatches', [
        'message_automation_id' => $recordatorio->id,
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'status' => 'pending',
    ]);
});

