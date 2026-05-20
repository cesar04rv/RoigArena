<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'evento_id' => $this->evento_id,
            'asiento_id' => $this->asiento_id,
            'reservado_hasta' => $this->reservado_hasta?->toISOString(),
            'estado' => $this->estado,
            'evento' => [
                'id' => $this->evento->id,
                'nombre' => $this->evento->nombre,
                'fecha' => $this->evento->fecha->format('d/m/Y'),
                'hora' => $this->evento->hora ? $this->evento->hora->format('H:i') : null,
            ],
            'asiento' => [
                'id' => $this->asiento->id,
                'fila' => $this->asiento->fila,
                'numero' => $this->asiento->numero,
                'nombre' => $this->asiento->nombreCompleto(),
                'sector' => [
                    'id' => $this->asiento->sector->id,
                    'nombre' => $this->asiento->sector->nombre,
                    'precio' => $this->asiento->sector->precio,
                ],
            ],
            'precio' => number_format(
                $this->evento->precioDelSector($this->asiento->sector_id)?->precio ?? 0,
                2, ',', '.'
            ) . ' €',
            'tiempo_restante_minutos' => $this->tiempoRestante(),
            'expira_en' => $this->reservado_hasta?->format('d/m/Y H:i:s'),
            'expirado' => $this->haExpirado(),
        ];
    }
}