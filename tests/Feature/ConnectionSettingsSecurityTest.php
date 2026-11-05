
<?php

use App\Filament\Pages\ConnectionSettings;
use App\Models\Company;
use App\Models\CommunicationConnection;
use App\Models\User;
use Livewire\Livewire;

it('impide cargar conexiones de otra empresa', function () {

    // Crear dos empresas para la prueba.
    $empresaA = Company::create([
        'name' => 'Empresa Test A',
    ]);

    $empresaB = Company::create([
        'name' => 'Empresa Test B',
    ]);

    // Crear un administrador de Empresa A.
    $adminA = User::create([
        'company_id' => $empresaA->id,
        'name' => 'Administrador Test A',
        'email' => 'admin-test-a@example.com',
        'password' => 'password-test-local',
        'is_admin' => true,
    ]);

    // Crear conexiones diferentes para cada empresa.
    CommunicationConnection::create([
        'company_id' => $empresaA->id,
        'channel' => 'telegram',
        'name' => 'Telegram Empresa A',
        'is_enabled' => false,
    ]);

    CommunicationConnection::create([
        'company_id' => $empresaB->id,
        'channel' => 'telegram',
        'name' => 'Telegram Empresa B',
        'is_enabled' => false,
    ]);

    // Simular un administrador de Empresa A.
    Livewire::actingAs($adminA)
        ->test(ConnectionSettings::class)

        // Debe cargar su propia empresa.
        ->assertSet('data.company_id', $empresaA->id)
        ->assertSet(
            'data.telegram.name',
            'Telegram Empresa A'
        )

        // Intentar seleccionar Empresa B.
        ->set('data.company_id', $empresaB->id)

        // El servidor debe restaurar Empresa A.
        ->assertSet('data.company_id', $empresaA->id)
        ->assertSet(
            'data.telegram.name',
            'Telegram Empresa A'
        );
});


it('no modifica conexiones de otra empresa', function () {

    // Crear dos empresas de prueba.
    $empresaA = Company::create([
        'name' => 'Empresa Seguridad A',
    ]);

    $empresaB = Company::create([
        'name' => 'Empresa Seguridad B',
    ]);

    // Crear administrador de Empresa A.
    $adminA = User::create([
        'company_id' => $empresaA->id,
        'name' => 'Administrador Seguridad A',
        'email' => 'seguridad-a@example.com',
        'password' => 'password-test-local',
        'is_admin' => true,
    ]);

    // Crear una conexion propia de Empresa B.
    $conexionB = CommunicationConnection::create([
        'company_id' => $empresaB->id,
        'channel' => 'telegram',
        'name' => 'Telegram Original Empresa B',
        'is_enabled' => false,
    ]);

    // Simular que Empresa A intenta cambiar
    // la empresa seleccionada y guardar.
    Livewire::actingAs($adminA)
        ->test(ConnectionSettings::class)
        ->set('data.company_id', $empresaB->id)
        ->set('data.telegram.name', 'Cambio desde Empresa A')
        ->call('save')
        ->assertSet('data.company_id', $empresaA->id);

    // Comprobar que Empresa B conserva sus datos.
    expect($conexionB->fresh()->name)
        ->toBe('Telegram Original Empresa B');
});


it('rechaza guardar conexiones de otra empresa', function () {

    $empresaA = Company::create([
        'name' => 'Empresa Protegida A',
    ]);

    $empresaB = Company::create([
        'name' => 'Empresa Protegida B',
    ]);

    $adminA = User::create([
        'company_id' => $empresaA->id,
        'name' => 'Administrador Protegido A',
        'email' => 'protegido-a@example.com',
        'password' => 'password-test-local',
        'is_admin' => true,
    ]);

    $conexionB = CommunicationConnection::create([
        'company_id' => $empresaB->id,
        'channel' => 'telegram',
        'name' => 'Telegram Original B',
        'is_enabled' => false,
    ]);

    // Simular la manipulacion del estado
    // completo del formulario.
    Livewire::actingAs($adminA)
        ->test(ConnectionSettings::class)
        ->set('data', [
            'company_id' => $empresaB->id,
            'telegram' => [
                'name' => 'Cambio no autorizado',
                'is_enabled' => false,
            ],
            'email' => [
                'name' => 'Email SMTP',
                'is_enabled' => false,
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'is_enabled' => false,
            ],
        ])
        ->call('save')
        ->assertForbidden();

    // La configuracion de B debe seguir intacta.
    expect($conexionB->fresh()->name)
        ->toBe('Telegram Original B');
});

