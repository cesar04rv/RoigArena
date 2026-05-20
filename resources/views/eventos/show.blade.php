@extends('layouts.app')

@section('title', '{{ $evento->nombre }} - Mi Arena')

@section('content')

<!-- INFORMACIÓN DEL EVENTO -->
<div class="hero">
    <h1>{{ $evento->nombre }}</h1>
    <p>{{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }} - {{ $evento->hora }}</p>
    <p>{{ $evento->descripcion_corta }}</p>
</div>

<!-- CONTENIDO PRINCIPAL -->
<div style="max-width: 900px; margin: 2rem auto;">
    
    <!-- IMAGEN DEL EVENTO -->
    @if($evento->poster_url)
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="{{ $evento->poster_url }}" alt="{{ $evento->nombre }}" 
                 style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        </div>
    @endif

    <!-- INFORMACIÓN DETALLADA -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="color: var(--secondary); margin-bottom: 1rem;">Información del Evento</h3>
        <p style="color: #64748b; line-height: 1.8; font-size: 1.05rem;">
            {{ $evento->descripcion_larga ?? $evento->descripcion_corta }}
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <div>
                <strong style="color: var(--secondary); display: block; margin-bottom: 0.5rem;">Fecha</strong>
                <p style="color: #64748b;">{{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }}</p>
            </div>
            <div>
                <strong style="color: var(--secondary); display: block; margin-bottom: 0.5rem;">Hora</strong>
                <p style="color: #64748b;">{{ $evento->hora }}</p>
            </div>
            <div>
                <strong style="color: var(--secondary); display: block; margin-bottom: 0.5rem;">Precio desde</strong>
                <p style="color: var(--primary); font-size: 1.3rem; font-weight: 700;">
                    {{ $precios->min('precio') }}€
                </p>
            </div>
        </div>
    </div>

    <!-- ARTISTAS -->
    @if($evento->artistas && count($evento->artistas) > 0)
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h3 style="color: var(--secondary); margin-bottom: 1rem;">Artistas</h3>
            <ul style="color: #64748b; line-height: 2;">
                @foreach($evento->artistas as $artista)
                    <li>{{ $artista->nombre }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- BOTÓN COMPRAR ENTRADAS -->
    <div style="text-align: center; margin-top: 3rem;">
        <a href="{{ route('compra.buy', $evento->id) }}" 
           class="btn btn-primary" 
           style="font-size: 1.2rem; padding: 1rem 3rem; display: inline-block;">
            Comprar Entradas
        </a>
    </div>

</div>

@endsection
