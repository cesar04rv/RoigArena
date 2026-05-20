<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LiberarReservasService;

//Limpiar reservas expiradas
class LimpiarReservasExpiradas extends Command
{
    protected $signature = 'reservas:limpiar';
    protected $description = 'Liberar reservas expiradas automáticamente';

    public function handle(LiberarReservasService $service)
    {
        $liberadas = $service->liberarExpiradas();
        
        if ($liberadas > 0) {
            $this->info("✅ Se liberaron {$liberadas} reservas expiradas");
        }
        
        return 0;
    }
}
