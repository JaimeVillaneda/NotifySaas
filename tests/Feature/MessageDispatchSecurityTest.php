
<?php

use App\Models\MessageDispatch;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('impide consultar envios de otra empresa', function () {

    // Administrador perteneciente a Empresa A.
    $adminA = new User([
        'company_id' => 1,
        'name' => 'Administrador Empresa A',
        'is_admin' => true,
    ]);

    // Envio perteneciente a Empresa A.
    $envioA = new MessageDispatch([
        'company_id' => 1,
    ]);

    // Envio perteneciente a Empresa B.
    $envioB = new MessageDispatch([
        'company_id' => 2,
    ]);

    // Puede consultar su propio historial.
    expect(
        Gate::forUser($adminA)->allows('view', $envioA)
    )->toBeTrue();

    // No puede consultar historial de otra empresa.
    expect(
        Gate::forUser($adminA)->allows('view', $envioB)
    )->toBeFalse();

    // Tampoco puede modificar o eliminar envios.
    expect(
        Gate::forUser($adminA)->allows('update', $envioA)
    )->toBeFalse();

    expect(
        Gate::forUser($adminA)->allows('delete', $envioA)
    )->toBeFalse();
});


it('muestra solo el historial de la empresa autenticada', function () {

    // Crear dos empresas temporales.
    $empresaA = \App\Models\Company::create([
        'name' => 'Empresa Historial A',
    ]);

    $empresaB = \App\Models\Company::create([
        'name' => 'Empresa Historial B',
    ]);

    // Crear administrador de Empresa A.
    $adminA = \App\Models\User::create([
        'company_id' => $empresaA->id,
        'name' => 'Administrador Historial A',
        'email' => 'historial-a@example.com',
        'password' => 'password-test-local',
        'is_admin' => true,
    ]);

    // Crear un cliente para cada empresa.
    $clienteA = \App\Models\Person::create([
        'company_id' => $empresaA->id,
        'first_name' => 'Cliente A',
        'phone' => '3001110001',
    ]);

    $clienteB = \App\Models\Person::create([
        'company_id' => $empresaB->id,
        'first_name' => 'Cliente B',
        'phone' => '3001110002',
    ]);

    // Registrar un envio de Empresa A.
    $envioA = MessageDispatch::create([
        'company_id' => $empresaA->id,
        'person_id' => $clienteA->id,
        'phone' => '3001110001',
        'channel' => 'telegram',
        'message' => 'Mensaje de Empresa A',
        'status' => 'pending',
    ]);

    // Registrar un envio de Empresa B.
    $envioB = MessageDispatch::create([
        'company_id' => $empresaB->id,
        'person_id' => $clienteB->id,
        'phone' => '3001110002',
        'channel' => 'telegram',
        'message' => 'Mensaje de Empresa B',
        'status' => 'pending',
    ]);

    // Simular el inicio de sesion de Empresa A.
    $this->actingAs($adminA);

    // Consultar los registros disponibles en Filament.
    $idsVisibles = \App\Filament\Resources\MessageDispatches\MessageDispatchResource::getEloquentQuery()
        ->pluck('id')
        ->all();

    // Solo debe aparecer el envio de Empresa A.
    expect($idsVisibles)->toEqual([$envioA->id]);

    // El envio B no debe ser visible.
    expect($idsVisibles)->not->toContain($envioB->id);
});
