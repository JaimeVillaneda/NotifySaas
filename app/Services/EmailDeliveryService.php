<?php

namespace App\Services;

use App\Models\MessageDispatch;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class EmailDeliveryService
{
    /**
     * Envia un recordatorio por correo electronico
     * utilizando el SMTP de la empresa.
     */
    public function send(MessageDispatch $dispatch): void
    {
        // PASO 1: Cargar cliente y conexion.
        $dispatch->loadMissing([
            'person',
            'connection',
            'company',
        ]);

        $person = $dispatch->person;
        $connection = $dispatch->connection;
        $company = $dispatch->company;

        // PASO 2: Validar datos necesarios.
        if (! $person || ! $connection || ! $company) {
            throw new RuntimeException(
                'Falta el cliente, la empresa o la conexion SMTP.'
            );
        }

        if (! $company->is_active || ! $person->is_active) {
            throw new RuntimeException(
                'La empresa o el cliente esta inactivo.'
            );
        }

        // PASO 3: Validar el canal.
        if (
            $dispatch->channel !== 'email' ||
            $connection->channel !== 'email'
        ) {
            throw new RuntimeException(
                'El envio no corresponde al canal Email.'
            );
        }

        if (! $connection->is_enabled) {
            throw new RuntimeException(
                'La conexion SMTP esta deshabilitada.'
            );
        }

        // PASO 4: Validar el aislamiento multiempresa.
        if (
            (int) $dispatch->company_id !== (int) $person->company_id ||
            (int) $dispatch->company_id !== (int) $connection->company_id
        ) {
            throw new RuntimeException(
                'Los registros pertenecen a empresas diferentes.'
            );
        }

        // PASO 5: Comprobar el destinatario.
        if (
            ! filter_var(
                $person->email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'El cliente no tiene un correo valido.'
            );
        }

        // PASO 6: Validar la configuracion SMTP.
        if (
            blank($connection->host) ||
            blank($connection->port) ||
            ! filter_var(
                $connection->from_address,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'La configuracion SMTP esta incompleta.'
            );
        }

        $encryption = $connection->settings['encryption']
            ?? 'tls';

        // Para este proyecto exigimos cifrado SMTP.
        if (! in_array($encryption, ['tls', 'ssl'], true)) {
            throw new RuntimeException(
                'La conexion SMTP debe utilizar TLS o SSL.'
            );
        }

        // PASO 7: Crear un transporte independiente
        // para la empresa, sin alterar el SMTP global.
        $mailer = Mail::build([
            'transport' => 'smtp',
            'scheme' => $encryption === 'ssl'
                ? 'smtps'
                : 'smtp',
            'host' => $connection->host,
            'port' => (int) $connection->port,
            'username' => $connection->username,
            'password' => $connection->secret,
            'timeout' => 15,
            'require_tls' => $encryption === 'tls',
        ]);

        // PASO 8: Enviar el correo.
        $mailer->raw(
            (string) $dispatch->message,
            function (Message $message) use (
                $person,
                $connection
            ): void {
                $message
                    ->to($person->email)
                    ->from(
                        $connection->from_address,
                        $connection->from_name
                            ?: 'NotifySaaS'
                    )
                    ->subject('Recordatorio - NotifySaaS');
            }
        );
    }
}
