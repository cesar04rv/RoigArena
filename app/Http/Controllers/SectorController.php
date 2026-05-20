<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\Sector;
use App\Models\Evento;
use App\Http\Resources\SectorResource;
use App\Services\SectorGeometryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SectorController extends Controller
{
    public function __construct(private SectorGeometryService $sectorGeometryService){
    }
    public function validarRectangulo(Request $request)
    {
        $validated = $request->validate([
            'inicio' => ['required', 'array'],
            'inicio.fila' => ['required', 'integer', 'min:1'],
            'inicio.columna' => ['required', 'integer', 'min:1'],
            'fin' => ['required', 'array'],
            'fin.fila' => ['required', 'integer', 'min:1'],
            'fin.columna' => ['required', 'integer', 'min:1'],
            'sector_id' => ['sometimes', 'integer', 'exists:sectores,id'],
        ]);

        $rectangulo = $this->sectorGeometryService->normalizarRectangulo(
            $validated['inicio'],
            $validated['fin']
        );

        $sectorId = $validated['sector_id'] ?? null;
        $haySolapamiento = $this->sectorGeometryService->existeSolapamiento($rectangulo, $sectorId);

        return response()->json([
            'data' => [
                'valido' => !$haySolapamiento,
                'solapamiento' => $haySolapamiento,
                'rectangulo' => $rectangulo,
                'total_asientos' => $rectangulo['total_asientos'],
            ],
        ]);
    }


    public function porEvento($eventoId, Request $request)
    {
        $evento = Evento::findOrFail($eventoId);

        // Cargamos los precios del evento para obtener los sectores asociados.
        $precios = $evento->precios()->with('sector')->get();

        $includeAsientos = $request->boolean('include_asientos', false);

        $data = $precios->map(function ($precio) use ($eventoId, $includeAsientos) {
            $sector = $precio->sector;
            if (!$sector) {
                return null;
            }

            $item = [
                'id' => $sector->id,
                'nombre' => $sector->nombre,
                'color_hex' => $sector->color_hex,
                'fila_inicio' => $sector->fila_inicio,
                'fila_fin' => $sector->fila_fin,
                'columna_inicio' => $sector->columna_inicio,
                'columna_fin' => $sector->columna_fin,
            ];

            if ($includeAsientos) {
                $asientos = $sector->asientos()
                    ->orderBy('fila')
                    ->orderBy('numero')
                    ->get()
                    ->map(function ($a) use ($eventoId) {
                        return [
                            'id' => $a->id,
                            'fila' => $a->fila,
                            'numero' => $a->numero,
                            'disponible' => $a->estaDisponible($eventoId),
                        ];
                    })
                    ->groupBy('fila')
                    ->map(function ($group) {
                        return array_values($group->toArray());
                    });

                $item['asientos'] = $asientos;
            }

            return $item;
        })->filter()->values();

        return response()->json(['data' => $data]);
    }

    public function index()
    {
        $sectores = Sector::activos()
            ->withCount('asientos')
            ->get();

        return response()->json([
            'data' => $sectores,
        ]);
    }

    public function store(Request $request) {
               $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:sectores,nombre',
            'descripcion' => 'nullable|string',
            'color_hex' => 'required|string|max:20',
            'activo' => 'sometimes|boolean',
            // Coordenadas del punto inicial del rectángulo del sector.
            'inicio.fila' => 'required|integer|min:1',
            'inicio.columna' => 'required|integer|min:1',
            // Coordenadas del punto final del rectángulo del sector.
            'fin.fila' => 'required|integer|min:1',
            'fin.columna' => 'required|integer|min:1',
        ]);

        // Normaliza las coordenadas para obtener siempre el rectángulo bien definido,
        // independientemente del orden en que se hayan enviado los puntos.
        $rectangulo = $this->sectorGeometryService->normalizarRectangulo(
            $validated['inicio'],
            $validated['fin']
        );

        // Comprueba si el nuevo sector se solapa con otro ya existente.
        if ($this->sectorGeometryService->existeSolapamiento($rectangulo)) {
            // Si hay solapamiento, se devuelve un error y no se crea nada.
            return response()->json([
                'message' => 'El rectángulo se solapa con otro sector.',
            ], 422);
        }

        // Ejecuta toda la creación dentro de una transacción:
        // si algo falla, se deshace todo automáticamente.
        return DB::transaction(function () use ($validated, $rectangulo) {
            // Crea el sector con los datos validados y las coordenadas calculadas.
            $sector = Sector::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'color_hex' => $validated['color_hex'],
                'activo' => $validated['activo'] ?? true,
                'fila_inicio' => $rectangulo['fila_inicio'],
                'fila_fin' => $rectangulo['fila_fin'],
                'columna_inicio' => $rectangulo['columna_inicio'],
                'columna_fin' => $rectangulo['columna_fin'],
                'cantidad_filas' => $rectangulo['cantidad_filas'],
                'cantidad_columnas' => $rectangulo['cantidad_columnas'],
            ]);

            // Genera e inserta todos los asientos que pertenecen al rectángulo del sector.
            Asiento::insert($this->sectorGeometryService->generarAsientos($sector));

            // Devuelve el sector creado con un mensaje de confirmación.
            return response()->json([
                'data' => $sector,
                'message' => 'Sector creado correctamente',
            ], 201);
        });
    }

    public function store_old(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:sectores',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $sector = Sector::create($request->all());

        return response()->json([
            'data' => $sector,
            'message' => 'Sector creado correctamente',
        ], 201);
    }

    /*
    * Acción para editar un sector. Esa acción debe permitir cambiar nombre, color y descripción sin tocar la geometría si no hace falta.
    * Actualizar sector (admin)
    */
    public function update(Request $request, $id)
    {
        $sector = Sector::findOrFail($id);

        // Validamos solo los campos que este endpoint puede aceptar.
        // - Los datos simples (nombre, descripción, color, activo) son cambios de ficha.
        // - inicio y fin solo se usan si también queremos mover o redimensionar la geometría.
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255', Rule::unique('sectores', 'nombre')->ignore($sector->id)],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'color_hex' => ['sometimes', 'string', 'max:20'],
            'activo' => ['sometimes', 'boolean'],
            'inicio' => ['sometimes', 'array'],
            'inicio.fila' => ['required_with:inicio,fin', 'integer', 'min:1'],
            'inicio.columna' => ['required_with:inicio,fin', 'integer', 'min:1'],
            'fin' => ['sometimes', 'array'],
            'fin.fila' => ['required_with:inicio,fin', 'integer', 'min:1'],
            'fin.columna' => ['required_with:inicio,fin', 'integer', 'min:1'],
        ]);
        $actualizarGeometria = $request->has('inicio') || $request->has('fin');

        if (!$actualizarGeometria) {
            $sector->fill($validated);
            $sector->save();

            return response()->json([
                'data' => $sector->fresh(),
                'message' => 'Sector actualizado correctamente',
            ]);
        }

        $rectangulo = $this->sectorGeometryService->normalizarRectangulo(
            $validated['inicio'],
            $validated['fin']
        );
        if ($this->sectorGeometryService->existeSolapamiento($rectangulo, $sector->id)) {
            return response()->json([
                'message' => 'El rectángulo se solapa con otro sector.',
            ], 422);
        }
        if ($this->sectorTieneReservasOVentas($sector)) {
            return response()->json([
                'message' => 'No se puede cambiar la geometría de un sector con reservas o ventas activas.',
            ], 422);
        }

           return DB::transaction(function () use ($sector, $validated, $rectangulo) {
            $sector->fill(array_merge(
                $validated,
                [
                    'fila_inicio' => $rectangulo['fila_inicio'],
                    'fila_fin' => $rectangulo['fila_fin'],
                    'columna_inicio' => $rectangulo['columna_inicio'],
                    'columna_fin' => $rectangulo['columna_fin'],
                    'cantidad_filas' => $rectangulo['cantidad_filas'],
                    'cantidad_columnas' => $rectangulo['cantidad_columnas'],
                ]
            ));
            $sector->save();

            $sector->asientos()->delete();

            Asiento::insert($this->sectorGeometryService->generarAsientos($sector));

            return response()->json([
                'data' => $sector->fresh(),
                'message' => 'Sector actualizado correctamente',
            ]);
        });

    }
    public function update_old(Request $request, $id)
    {
        $sector = Sector::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:255|unique:sectores,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $sector->update($request->all());

        return response()->json([
            'data' => $sector,
            'message' => 'Sector actualizado correctamente',
        ]);
    }
    public function destroy($id)
    {
        $sector = Sector::findOrFail($id);

        // Comprobamos si el sector tiene reservas vigentes o ventas asociadas.
        if ($this->sectorTieneReservasOVentas($sector)) {
            return response()->json([
                'message' => 'No se puede eliminar un sector con reservas o ventas activas.',
            ], 422);
        }

        // Si no tiene reservas ni ventas, procedemos a eliminar el sector y sus asientos.
        return DB::transaction(function () use ($sector) {
            // Eliminamos primero los asientos para evitar problemas de integridad referencial.
            $sector->asientos()->delete();

            // Luego eliminamos el sector.
            $sector->delete();

            return response()->json([
                'message' => 'Sector eliminado correctamente',
            ]);
        });
    }

    public function destroy_old($id)
    {
        $sector = Sector::findOrFail($id);

        // Verificar que no tenga asientos
        if ($sector->totalAsientos() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar un sector con asientos',
            ], 400);
        }

        $sector->delete();

        return response()->json([
            'message' => 'Sector eliminado correctamente',
        ]);
    }

    public function buscar(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([
                'error' => 'El parámetro "q" es requerido',
            ], 400);
        }

        $sectores = Sector::where('nombre', 'like', "%{$query}%")
            ->with('eventos')
            ->get();

        return response()->json([
            'data' => SectorResource::collection($sectores),
        ]);
    }

    private function sectorTieneReservasOVentas(Sector $sector): bool
    {
        return $sector->asientos()
            ->where(function ($query) {
                $query->whereHas('estadoAsientos', function ($estadoQuery) {
                    $estadoQuery->where(function ($estadoSubQuery) {
                        $estadoSubQuery->where('estado', 'RESERVADO')
                            ->where('reservado_hasta', '>', now());
                    })->orWhere('estado', 'OCUPADO');
                })
                    ->orWhereHas('entradas');
            })
            ->exists();
    }
}
