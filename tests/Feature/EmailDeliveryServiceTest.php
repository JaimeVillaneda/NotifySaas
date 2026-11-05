<?php

use App\Models\Company;
use App\Models\CommunicationConnection;
use App\Models\MessageDispatch;
use App\Models\Person;
use App\Services\EmailDeliveryService;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

it('prepara un correo usando el SMTP de la empresa', function () {

    // Crear una empresa.
    $empresa = Company::create([
        'name' => 'Empresa Email Prueba',
        'is_active' => true,
    ]);

    // Crear un cliente con correo.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Carlos',
        'last_name' => 'Correo',
        'phone' => '3005551234',
        'email' => 'carlos@example.com',
        'is_active' => true,
    ]);

    // Crear la conexion SMTP.
    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'SMTP Prueba',
        'channel' => 'email',
        'is_enabled' => true,
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'usuario@example.com',
        'secret' => 'CLAVE-SIMULADA',
        'from_address' => 'avisos@example.com',
        'from_name' => 'NotifySaaS Prueba',
        'settings' => [
            'encryption' => 'tls',
        ],
    ]);

    // Crear un envio pendiente.
    $envio = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $cliente->phone,
        'channel' => 'email',
        'message' => 'Hola Carlos, tienes un recordatorio.',
        'status' => 'pending',
    ]);

    // Simular un transporte de correo.
    // No estableceremos una conexion SMTP real.
    $mailerSimulado = Mockery::mock(Mailer::class);

    // Verificar los datos del correo.
    $mailerSimulado
        ->shouldReceive('raw')
        ->once()
        ->with(
            'Hola Carlos, tienes un recordatorio.',
            Mockery::on(function ($callback) {

                $email = new Email();

                $callback(new Message($email));

                return $email->getTo()[0]->getAddress()
                        === 'carlos@example.com'
                    && $email->getFrom()[0]->getAddress()
                        === 'avisos@example.com'
                    && $email->getSubject()
                        === 'Recordatorio - NotifySaaS';
            })
        );

    // Comprobar la configuracion SMTP utilizada.
    Mail::shouldReceive('build')
        ->once()
        ->with(Mockery::on(function ($config) {

            return $config['transport'] === 'smtp'
                && $config['host'] === 'smtp.example.com'
                && $config['port'] === 587
                && $config['username'] === 'usuario@example.com'
                && $config['scheme'] === 'smtp'
                && $config['require_tls'] === true;
        }))
        ->andReturn($mailerSimulado);

    // Ejecutar el servicio.
    app(EmailDeliveryService::class)->send($envio);
});
