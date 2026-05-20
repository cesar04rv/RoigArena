<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCancelacion extends Model
{
    protected $table = 'solicitudes_cancelacion';

    protected $fillable = [
        'entrada_id',
        'usuario_id',
        'motivo_usuario',
        'estado',
        'motivo_rechazo',
        'procesada_por',
        'procesada_at',
    ];

    protected $casts = [
        'procesada_at' => 'datetime',
    ];

    // ============================================
    // RELACIONES
    // ============================================

    public function entrada()
    {
        return $this->belongsTo(Entrada::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function procesadaPor()
    {
        return $this->belongsTo(User::class, 'procesada_por');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'rechazada');
    }

    // ============================================
    // HELPERS
    // ============================================

    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function esAprobada(): bool
    {
        return $this->estado === 'aprobada';
    }

    public function esRechazada(): bool
    {
        return $this->estado === 'rechazada';
    }
}
