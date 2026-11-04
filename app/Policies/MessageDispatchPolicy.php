<?php

namespace App\Policies;

use App\Models\MessageDispatch;
use App\Models\User;

class MessageDispatchPolicy
{
    // Permitir consultar el módulo a administradores.
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    // Permitir consultar solo registros autorizados.
    public function view(
        User $user,
        MessageDispatch $dispatch
    ): bool {
        if (! $user->is_admin) {
            return false;
        }

        // Administrador general temporal.
        if ($user->company_id === null) {
            return true;
        }

        // El envío debe pertenecer a su empresa.
        return $dispatch->company_id !== null
            && (int) $user->company_id === (int) $dispatch->company_id;
    }

    // El historial se genera mediante el sistema.
    public function create(User $user): bool
    {
        return false;
    }

    // No permitir modificar registros históricos.
    public function update(
        User $user,
        MessageDispatch $dispatch
    ): bool {
        return false;
    }

    // No permitir eliminar registros históricos.
    public function delete(
        User $user,
        MessageDispatch $dispatch
    ): bool {
        return false;
    }

    // Bloquear la eliminación masiva.
    public function deleteAny(User $user): bool
    {
        return false;
    }
}
