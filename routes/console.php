<?php

use App\Models\EstadoAsiento;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Comando inline para liberar reservas expiradas
Artisan::command('reservas:liberar-expiradas', function () {
    $liberadas = EstadoAsiento::expirados()->delete();
    $this->info("✅ Reservas expiradas liberadas: {$liberadas}");
})->purpose('Libera reservas vencidas de asientos bloqueados');

// Programar limpieza automática cada minuto
Schedule::command('reservas:liberar-expiradas')->everyMinute();
