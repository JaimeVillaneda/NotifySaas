
<?php

use App\Filament\Resources\People\Pages\EditPerson;
use App\Models\Company;
use App\Models\Person;
use App\Models\User;
use Livewire\Livewire;

it('permite desactivar un cliente sin eliminarlo', function () {

    // Crear una empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa Prueba Clientes',
    ]);

    // Crear administrador de esa empresa.
    $admin = User::create([
        'company_id' => $empresa->id,
        'name' => 'Administrador Clientes',
        'email' => 'admin-clientes@example.com',
        'password' => 'password-test-local',
        'is_admin' => true,
    ]);

    // Crear un cliente activo.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Carlos',
        'last_name' => 'Prueba',
        'phone' => '3001234567',
        'preferred_channel' => 'email',
        'is_active' => true,
    ]);

    // Verificar que inicialmente está activo.
    expect($cliente->is_active)->toBeTrue();

    // Simular que el administrador lo desactiva
    // desde el formulario de Filament.
    Livewire::actingAs($admin)
        ->test(EditPerson::class, [
            'record' => $cliente->getRouteKey(),
        ])
        ->set('data.is_active', false)
        ->call('save')
        ->assertHasNoFormErrors();

    // Consultar nuevamente la base de datos.
    $cliente->refresh();

    // Comprobar que quedó inactivo.
    expect($cliente->is_active)->toBeFalse();

    // Comprobar que el cliente NO fue eliminado.
    $this->assertDatabaseHas('people', [
        'id' => $cliente->id,
        'company_id' => $empresa->id,
        'is_active' => 0,
    ]);
});


it('protege clientes de otras empresas', function () {

    // Crear dos empresas independientes.
    $empresaA = Company::create([
        'name' => 'Empresa Clientes A',
    ]);

    $empresaB = Company::create([
        'name' => 'Empresa Clientes B',
    ]);

    // Administrador de Empresa A.
    $adminA = User::create([
        'company_id' => $empresaA->id,
        'name' => 'Administrador A',
        'email' => 'clientes-a@example.com',
        'password' => 'password-test-local',
        'is_admin' => true,
    ]);

    // Cliente de Empresa A.
    $clienteA = Person::create([
        'company_id' => $empresaA->id,
        'first_name' => 'Cliente A',
        'phone' => '3005550001',
        'is_active' => true,
    ]);

    // Cliente de Empresa B.
    $clienteB = Person::create([
        'company_id' => $empresaB->id,
        'first_name' => 'Cliente B',
        'phone' => '3005550002',
        'is_active' => true,
    ]);

    // Iniciar sesión como administrador de A.
    $this->actingAs($adminA);

    // Comprobar permisos.
    expect(
        \Illuminate\Support\Facades\Gate::allows(
            'update',
            $clienteA
        )
    )->toBeTrue();

    expect(
        \Illuminate\Support\Facades\Gate::allows(
            'update',
            $clienteB
        )
    )->toBeFalse();

    // Consultar los clientes visibles en Filament.
    $idsVisibles = \App\Filament\Resources\People\PersonResource::getEloquentQuery()
        ->pluck('id')
        ->all();

    // Debe aparecer solamente el cliente de A.
    expect($idsVisibles)->toEqual([$clienteA->id]);

    // Confirmar que B conserva su estado.
    expect($clienteB->fresh()->is_active)->toBeTrue();
});

