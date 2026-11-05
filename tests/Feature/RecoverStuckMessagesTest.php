<?php

use App\Models\Company;
use App\Models\MessageDispatch;
use App\Models\Person;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('recupera mensajes interrumpidos sin afectar otros envios', function () {

    // PASO 1: Crear empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa Recuperacion',
        'is_active' => true,
    ]);

    // PASO 2: Crear cliente.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente',
        'last_name' => 'Recuperacion',
        'phone' => '3006660001',
        'is_active' => true,
    ]);

    // PASO 3: Crear un mensaje interrumpido.
    $interrumpido = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'phone' => $cliente->phone,
        'channel' => 'telegram',
        'message' => 'Mensaje interrumpido',
        'status' => 'processing',
        'attempts' => 1,
    ]);

    // Simular que lleva 45 minutos sin actualizarse.
    DB::table('message_dispatches')
        ->where('id', $interrumpido->id)
        ->update([
            'updated_at' => now()->subMinutes(45),
        ]);

    // PASO 4: Crear un mensaje en proceso reciente.
    $reciente = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'phone' => $cliente->phone,
        'channel' => 'telegram',
        'message' => 'Mensaje reciente',
        'status' => 'processing',
        'attempts' => 1,
    ]);

    // PASO 5: Crear un mensaje pendiente.
    $pendiente = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'phone' => $cliente->phone,
        'channel' => 'telegram',
        'message' => 'Mensaje pendiente',
        'status' => 'pending',
        'attempts' => 0,
    ]);

    // PASO 6: Ejecutar el comando de recuperacion.
    $resultado = Artisan::call('messages:recover');

    expect($resultado)->toBe(0);

    // PASO 7: Verificar el mensaje interrumpido.
    $interrumpido->refresh();

    expect($interrumpido->status)->toBe('failed');

    expect($interrumpido->error)
        ->toContain('no confirmado');

    // No debe incrementar los intentos.
    expect($interrumpido->attempts)->toBe(1);

    // PASO 8: El mensaje reciente sigue procesandose.
    expect($reciente->fresh()->status)
        ->toBe('processing');

    // PASO 9: El mensaje pendiente no cambia.
    expect($pendiente->fresh()->status)
        ->toBe('pending');

    // PASO 10: No se eliminaron registros.
    expect(MessageDispatch::count())->toBe(3);

    // PASO 11: Ejecutar nuevamente.
    expect(Artisan::call('messages:recover'))->toBe(0);

    // El registro recuperado no debe alterarse.
    expect($interrumpido->fresh()->attempts)->toBe(1);

    expect(MessageDispatch::count())->toBe(3);
});
