<?php

namespace App\Console\Commands;

use App\Models\MessageDispatch;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('messages:recover')]
#[Description('Detect interrupted messages requiring manual review.')]
class RecoverStuckMessages extends Command
{
    /**
     * Identifica mensajes interrumpidos.
     *
     * No vuelve a enviar mensajes.
     * Un mensaje pudo haber llegado al proveedor
     * aunque el sistema no registrara el resultado.
     */
    public function handle(): int
    {
        // Considerar interrumpidos los mensajes
        // que llevan mas de 30 minutos procesandose.
        $limit = now()->subMinutes(30);

        $recovered = 0;

        // Buscar registros que siguen en processing.
        MessageDispatch::query()
            ->where('status', 'processing')
            ->where('updated_at', '<=', $limit)
            ->chunkById(100, function ($dispatches) use (
                $limit,
                &$recovered
            ): void {

                foreach ($dispatches as $dispatch) {

                    // Comprobar nuevamente el estado
                    // antes de modificar el registro.
                    $updated = MessageDispatch::query()
                        ->whereKey($dispatch->id)
                        ->where('status', 'processing')
                        ->where('updated_at', '<=', $limit)
                        ->update([
                            'status' => 'failed',
                            'error' =>
                                'Resultado del envio no confirmado. '
                                . 'El procesamiento se interrumpio. '
                                . 'Verifique el proveedor antes de reenviar.',
                        ]);

                    if ($updated === 1) {
                        $recovered++;

                        $this->warn(
                            "Envio {$dispatch->id}: "
                            . "requiere revision manual."
                        );
                    }
                }
            });

        // Mostrar el resumen.
        $this->info(
            "Mensajes pendientes de revision: {$recovered}"
        );

        return self::SUCCESS;
    }
}
