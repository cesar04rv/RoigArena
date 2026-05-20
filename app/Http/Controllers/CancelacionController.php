<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\SolicitudCancelacion;
use App\Models\EstadoAsiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelacionController extends Controller
{
    // ============================================
    // ACCIONES DEL USUARIO
    // ============================================

    /**
     * El usuario solicita la cancelación de una entrada.
     * No cancela directamente: crea una solicitud pendiente para el admin.
     */
    public function solicitar(Request $request, $entradaId)
    {
        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        $entrada = Entrada::where('id', $entradaId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // No permitir si ya está descargada
        if ($entrada->descargada) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar una entrada ya descargada.',
            ], 409);
        }

        // No permitir si ya tiene una solicitud pendiente
        $yaExiste = SolicitudCancelacion::where('entrada_id', $entradaId)
            ->where('estado', 'pendiente')
            ->exists();

        if ($yaExiste) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una solicitud de cancelación pendiente para esta entrada.',
            ], 409);
        }

        SolicitudCancelacion::create([
            'entrada_id'    => $entradaId,
            'usuario_id'    => auth()->id(),
            'motivo_usuario' => $request->motivo,
            'estado'        => 'pendiente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tu solicitud de cancelación ha sido enviada. El administrador la revisará en breve.',
        ]);
    }

    /**
     * El usuario consulta el estado de sus solicitudes de cancelación.
     */
    public function misSolicitudes(Request $request)
    {
        $solicitudes = SolicitudCancelacion::where('usuario_id', auth()->id())
            ->with(['entrada.evento', 'entrada.asiento.sector'])
            ->latest()
            ->get()
            ->map(function ($sol) {
                $entrada = $sol->entrada;
                $asiento = $entrada?->asiento;
                return [
                    'id'             => $sol->id,
                    'estado'         => $sol->estado,
                    'motivo_usuario' => $sol->motivo_usuario,
                    'motivo_rechazo' => $sol->motivo_rechazo,
                    'created_at'     => $sol->created_at->format('d/m/Y H:i'),
                    'procesada_at'   => $sol->procesada_at?->format('d/m/Y H:i'),
                    'entrada' => $entrada ? [
                        'id'      => $entrada->id,
                        'evento'  => $entrada->evento?->nombre,
                        'fecha'   => $entrada->evento?->fecha?->format('d/m/Y'),
                        'asiento' => $asiento
                            ? ($asiento->sector?->nombre . ' - Fila ' . $asiento->fila . ' - Nº ' . $asiento->numero)
                            : 'Asiento no disponible',
                        'precio'  => $entrada->precioFormateado(),
                    ] : null,
                ];
            });

        return response()->json(['data' => $solicitudes]);
    }

    // ============================================
    // ACCIONES DEL ADMINISTRADOR
    // ============================================

    /**
     * Admin: listado de todas las solicitudes de cancelación.
     */
    public function adminIndex(Request $request)
    {
        $estado = $request->get('estado', 'pendiente');

        $solicitudes = SolicitudCancelacion::with([
                'entrada.evento',
                'entrada.asiento.sector',
                'usuario',
                'procesadaPor',
            ])
            ->when($estado !== 'todas', fn($q) => $q->where('estado', $estado))
            ->latest()
            ->get();

        $contadores = [
            'pendiente' => SolicitudCancelacion::pendientes()->count(),
            'aprobada'  => SolicitudCancelacion::aprobadas()->count(),
            'rechazada' => SolicitudCancelacion::rechazadas()->count(),
        ];

        return view('admin.cancelaciones.index', compact('solicitudes', 'estado', 'contadores'));
    }

    /**
     * Admin: aprobar una solicitud de cancelación.
     * Libera el asiento y elimina la entrada.
     */
    public function aprobar($id)
    {
        $solicitud = SolicitudCancelacion::with('entrada.asiento')->findOrFail($id);

        if (!$solicitud->esPendiente()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya ha sido procesada.',
            ], 409);
        }

        DB::transaction(function () use ($solicitud) {
            $entrada = $solicitud->entrada;

            // Liberar el asiento
            if ($entrada) {
                $estadoAsiento = $entrada->asiento->estadoAsientos()
                    ->where('evento_id', $entrada->evento_id)
                    ->where('estado', 'OCUPADO')
                    ->latest('id')
                    ->first();

                if ($estadoAsiento) {
                    $estadoAsiento->update([
                        'estado'         => 'DISPONIBLE',
                        'user_id'        => null,
                        'reservado_hasta' => null,
                    ]);
                }

                // Comprobar aforo del evento
                $evento = $entrada->evento;
                $entrada->delete();
                if ($evento) {
                    $evento->comprobarEvento();
                }
            }

            // Marcar solicitud como aprobada
            $solicitud->update([
                'estado'       => 'aprobada',
                'procesada_por' => auth()->id(),
                'procesada_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Cancelación aprobada. El asiento ha sido liberado.',
        ]);
    }

    /**
     * Admin: rechazar una solicitud de cancelación.
     * La entrada sigue activa. El admin debe indicar el motivo.
     */
    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|max:500',
        ]);

        $solicitud = SolicitudCancelacion::findOrFail($id);

        if (!$solicitud->esPendiente()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya ha sido procesada.',
            ], 409);
        }

        $solicitud->update([
            'estado'         => 'rechazada',
            'motivo_rechazo' => $request->motivo_rechazo,
            'procesada_por'  => auth()->id(),
            'procesada_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada.',
        ]);
    }
}
