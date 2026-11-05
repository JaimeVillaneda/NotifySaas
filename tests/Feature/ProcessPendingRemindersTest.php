
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
