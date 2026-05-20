<?php

use App\Http\Controllers\AsientoController;
use App\Http\Controllers\CancelacionController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

// RUTAS PÚBLICAS
Route::get('/eventos', [EventoController::class, 'index']);
Route::get('/eventos/{id}', [EventoController::class, 'show']);
Route::get('/eventos/{eventoId}/asientos', [AsientoController::class, 'porEvento']);
Route::get('/eventos/{eventoId}/sectores/{sectorId}/asientos', [AsientoController::class, 'porSector']);

// RUTAS AUTENTICADAS (Sanctum token O sesión web)
Route::middleware(['auth:sanctum,web'])->group(function () {
    // Reservas
    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);

    // Compras
    Route::post('/compras', [CompraController::class, 'store']);

    // Entradas
    Route::post('/entradas/{id}/descargar', [EntradaController::class, 'marcarDescargada']);
    Route::delete('/entradas/{id}/cancelar', [EntradaController::class, 'cancelarCompra']);

    // Solicitudes de cancelación (usuario)
    Route::post('/entradas/{id}/solicitar-cancelacion', [CancelacionController::class, 'solicitar']);
    Route::get('/mis-solicitudes-cancelacion', [CancelacionController::class, 'misSolicitudes']);

    Route::get('/eventos/{evento}/asientos', [EventoController::class, 'mostrarTodosLosAsientos']);
});