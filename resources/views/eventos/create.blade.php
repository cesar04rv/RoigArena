@extends('layouts.app')

@section('title', 'Crear Evento - Mi Arena')

@section('content')

<div class="hero">
    <h1>CREAR NUEVO EVENTO</h1>
    <p>Panel de administración</p>
</div>

<div style="max-width: 900px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los siguientes errores:</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.eventos.store', [], false) }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="nombre">Nombre del evento</label>
            <input id="nombre" name="nombre" type="text" class="form-control" value="{{ old('nombre') }}" required placeholder="Ej: Final Copa del Rey">
        </div>

        <div class="form-group">
            <label class="form-label" for="descripcion_corta">Descripción corta</label>
            <input id="descripcion_corta" name="descripcion_corta" type="text" class="form-control" value="{{ old('descripcion_corta') }}" maxlength="255" required placeholder="Breve descripción del evento">
        </div>

        <div class="form-group">
            <label class="form-label" for="descripcion_larga">Descripción larga</label>
            <textarea id="descripcion_larga" name="descripcion_larga" rows="4" class="form-control" required placeholder="Descripción completa del evento">{{ old('descripcion_larga') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label" for="fecha">Fecha</label>
                <input id="fecha" name="fecha" type="date" class="form-control" value="{{ old('fecha') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="hora">Hora</label>
                <input id="hora" name="hora" type="time" class="form-control" value="{{ old('hora') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="poster_url">URL del póster</label>
            <input id="poster_url" name="poster_url" type="url" class="form-control" value="{{ old('poster_url') }}" placeholder="https://...">
        </div>

        <div class="form-group">
            <label class="form-label" for="poster_ancho_url">URL del póster ancho</label>
            <input id="poster_ancho_url" name="poster_ancho_url" type="url" class="form-control" value="{{ old('poster_ancho_url') }}" placeholder="https://...">
        </div>

        @php
            $oldSectores = old('sectores', []);
            $oldPrecios = old('precios', []);
            $oldSelectedSectors = collect($oldSectores)->map(function ($sectorId, $index) use ($sectoresDisponibles, $oldPrecios) {
                $sector = $sectoresDisponibles->firstWhere('id', $sectorId);
                return [
                    'id' => $sectorId,
                    'nombre' => $sector ? $sector->nombre : "Sector {$sectorId}",
                    'precio' => $oldPrecios[$index] ?? '',
                ];
            });
        @endphp

        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 2px solid var(--border); margin-top: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem; color: var(--secondary);">Sectores disponibles</h3>
            <p style="color: #64748b; margin-bottom: 1rem;">Selecciona los sectores que estarán disponibles en este evento y asigna su precio.</p>

            <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: end;">
                <div class="form-group">
                    <label class="form-label" for="sector_selector">Sector</label>
                    <select id="sector_selector" class="form-control">
                        <option value="">Selecciona un sector</option>
                        @foreach ($sectoresDisponibles as $sector)
                            <option value="{{ $sector->id }}" data-nombre="{{ $sector->nombre }}">{{ $sector->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" id="addSectorButton" class="btn btn-secondary" style="margin-bottom: 1.2rem;">Añadir sector</button>
            </div>

            <div id="selectedSectorsContainer" style="margin-top: 1rem; display: grid; gap: 0.75rem;"></div>
            <div id="sectorEmptyNotice" style="display: none; margin-top: 1rem; color: #64748b; font-size: 0.95rem;">
                No has añadido ningún sector todavía.
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Crear Evento</button>
            <a href="{{ route('home') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

</div>

@endsection

@section('page_scripts')
<script>
    const selectedSectorsData = [];
    @foreach ($oldSelectedSectors as $item)
        selectedSectorsData.push({
            id: "{{ $item['id'] }}",
            nombre: "{{ $item['nombre'] }}",
            precio: "{{ $item['precio'] }}"
        });
    @endforeach

    const sectorsContainer = document.getElementById('selectedSectorsContainer');
    const emptyNotice = document.getElementById('sectorEmptyNotice');
    const addButton = document.getElementById('addSectorButton');
    const selectorEl = document.getElementById('sector_selector');

    function renderSectors() {
        sectorsContainer.innerHTML = '';
        if (selectedSectorsData.length === 0) {
            emptyNotice.style.display = 'block';
            return;
        }
        emptyNotice.style.display = 'none';

        selectedSectorsData.forEach((s, idx) => {
            const row = document.createElement('div');
            row.style.cssText = 'display:grid;grid-template-columns:1fr 150px auto;gap:1rem;align-items:center;background:white;padding:1rem;border-radius:6px;border:1px solid var(--border);';
            
            row.innerHTML = `
                <div>
                    <strong style="color: var(--secondary);">${s.nombre}</strong>
                    <input type="hidden" name="sectores[]" value="${s.id}">
                </div>
                <div>
                    <input type="number" name="precios[]" value="${s.precio}" 
                           placeholder="Precio" min="0" step="0.01" required
                           class="form-control" style="margin: 0;">
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSector(${idx})">
                    Eliminar
                </button>
            `;
            sectorsContainer.appendChild(row);
        });
    }

    function removeSector(index) {
        selectedSectorsData.splice(index, 1);
        renderSectors();
    }

    addButton.addEventListener('click', () => {
        const id = selectorEl.value;
        const nombre = selectorEl.options[selectorEl.selectedIndex]?.dataset?.nombre;
        if (!id || !nombre) {
            alert('Selecciona un sector válido');
            return;
        }
        if (selectedSectorsData.some(s => s.id === id)) {
            alert('Este sector ya está añadido');
            return;
        }
        selectedSectorsData.push({ id, nombre, precio: '' });
        renderSectors();
        selectorEl.value = '';
    });

    renderSectors();
</script>
@endsection
