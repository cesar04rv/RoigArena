<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\Precio;
use App\Models\Sector;
use App\Models\Asiento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventoController extends Controller
{
    /**
     * Listar eventos futuros (público)
     */
    public function index()
    {
        $eventos = Evento::futuros()
            ->with(['artistas', 'sectoresDisponibles'])
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'data' => $eventos
        ]);
    }

    /**
     * Mostrar detalle de un evento (público)
     */
    public function show($id)
    {
        $evento = Evento::with(['artistas', 'sectoresDisponibles.precios'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'evento' => $evento,
                'sectores' => $evento->sectoresDisponibles->map(function ($sector) use ($evento) {
                    $precio = $sector->precios()->where('evento_id', $evento->id)->first();
                    return [
                        'id' => $sector->id,
                        'nombre' => $sector->nombre,
                        'precio' => $precio ? $precio->precio : null,
                        'disponibles' => $precio ? $precio->disponibles : 0,
                    ];
                })
            ]
        ]);
    }

    /**
     * Mostrar formulario de creación de evento (ADMIN)
     */
    public function create()
    {
        $artistas = Artista::all();
        $sectores = Sector::all();
        $sectoresDisponibles = Sector::all();
        return view('eventos.create', compact('artistas', 'sectores', 'sectoresDisponibles'));
    }

    /**
     * Editor visual de sectores (ADMIN)
     */
    public function sectorEditor($eventoId)
    {
        $evento = Evento::with(['sectoresDisponibles', 'artistas'])->findOrFail($eventoId);
        $sectores = Sector::all();
        $artistas = Artista::all();

        $sectoresAsignados = $evento->sectoresDisponibles->map(function ($sector) use ($evento) {
            $precio = Precio::where('evento_id', $evento->id)
                ->where('sector_id', $sector->id)
                ->first();
            return [
                'sector_id' => $sector->id,
                'nombre' => $sector->nombre,
                'precio' => $precio ? $precio->precio : 0,
                'disponibles' => $precio ? $precio->disponibles : 0
            ];
        });

        return view('eventos.sectores-editor', compact('evento', 'sectores', 'artistas', 'sectoresAsignados'));
    }

    /**
     * Guardar nuevo evento (ADMIN)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion_corta' => 'nullable|string|max:255',
            'descripcion_larga' => 'nullable|string',
            'fecha' => 'required|date',
            'hora' => 'nullable',
            'poster_url' => 'nullable|url',
            'poster_ancho_url' => 'nullable|url',
            'artistas' => 'nullable|array',
            'artistas.*' => 'exists:artistas,id',
            'sectores' => 'nullable|array',
            'sectores.*' => 'exists:sectores,id',
            'precios' => 'nullable|array',
            'precios.*' => 'nullable|numeric|min:0',
            'disponibles' => 'nullable|array',
            'disponibles.*' => 'nullable|integer|min:0'
        ]);

        $evento = Evento::create([
            'nombre' => $validated['nombre'],
            'descripcion_corta' => $validated['descripcion_corta'] ?? '',
            'descripcion_larga' => $validated['descripcion_larga'] ?? '',
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'] ?? '20:00',
            'ubicacion' => 'Roig Arena',
            'poster_url' => $validated['poster_url'] ?? null,
            'poster_ancho_url' => $validated['poster_ancho_url'] ?? null,
            'estado' => 'activo'
        ]);

        // Asociar artistas
        if (!empty($validated['artistas'])) {
            $evento->artistas()->sync($validated['artistas']);
        }

        // Asociar sectores con precios
        if (!empty($validated['sectores'])) {
            foreach ($validated['sectores'] as $index => $sectorId) {
                $precio = $validated['precios'][$index] ?? 0;
                $disponibles = $validated['disponibles'][$index] ?? 100;

                Precio::create([
                    'evento_id' => $evento->id,
                    'sector_id' => $sectorId,
                    'precio' => $precio,
                    'disponibles' => $disponibles
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Evento creado correctamente',
                'data' => $evento->load(['artistas', 'sectoresDisponibles'])
            ], 201);
        }
        return redirect()->route('admin.eventos.create')
            ->with('success', 'Evento creado correctamente');
    }

    /**
     * Eventos del usuario autenticado
     */
    public function misEventos()
    {
        $user = auth()->user();
        $entradas = $user->entradas()->with('evento')->get();
        $miseventos = $entradas->pluck('evento')->unique('id')->values();

        return view('auth.mis-eventos', compact('miseventos'));
    }

    /**
     * Información de tickets del usuario (lista detallada)
     */
    public function misEventosInfo()
    {
        $user = auth()->user();
        $entradas = $user->entradas()->with(['evento', 'asiento.sector'])->get();

        $miseventos = $entradas->groupBy('evento_id')->map(function ($entradasGrupo) {
            $evento = $entradasGrupo->first()->evento;
            $evento->setRelation('entradas', $entradasGrupo);
            return $evento;
        })->values();

        return view('auth.mis-eventos-info', compact('miseventos'));
    }

    /**
     * Detalle de entradas del usuario para un evento concreto
     */
    public function miEventoInfo($id)
    {
        $user = auth()->user();

        $evento = Evento::with(['entradas' => function ($q) use ($user) {
            $q->where('user_id', $user->id)->with('asiento.sector');
        }])->findOrFail($id);

        return view('auth.mi-evento-info', compact('evento'));
    }

    /**
     * Mostrar todos los asientos de un evento
     */
    public function mostrarTodosLosAsientos(Evento $evento)
    {
        $asientos = Asiento::all()->map(function ($asiento) use ($evento) {
            $disponible = $asiento->estaDisponibleParaEvento($evento->id);

            return [
                'id' => $asiento->id,
                'fila' => $asiento->fila,
                'numero' => $asiento->numero,
                'sector_id' => $asiento->sector_id,
                'sector_nombre' => $asiento->sector->nombre,
                'disponible' => $disponible,
                'estado' => $disponible ? 'disponible' : 'ocupado'
            ];
        });

        return response()->json([
            'data' => [
                'total_filas' => 12,
                'total_columnas' => 20,
                'asientos' => $asientos
            ]
        ]);
    }
}