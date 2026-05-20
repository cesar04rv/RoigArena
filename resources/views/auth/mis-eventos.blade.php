@extends('layouts.app')

@section('title', 'Mis Eventos | Roig Arena')

@section('page_styles')
<style>
.eventos-header {
    background: linear-gradient(135deg, var(--secondary) 0%, #0f172a 100%);
    color: white;
    text-align: center;
    padding: 3rem 1rem;
    margin-bottom: 2rem;
    border-radius: 12px;
}

.eventos-header h1 {
    font-family: Impact, sans-serif;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.eventos-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }
}

.event-card {
    display: block;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
    color: inherit;
}

.event-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.event-card-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    display: block;
}

.event-card-body {
    padding: 1.5rem;
}

.event-card-title {
    font-family: Impact, sans-serif;
    color: var(--primary);
    margin-bottom: 0.75rem;
    font-size: 1.4rem;
    margin-top: 0;
}

.event-meta {
    color: #64748b;
    font-size: 0.95rem;
    margin: 0;
}

.event-meta strong {
    color: var(--secondary);
}

.card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-body h2 {
    font-family: Impact, sans-serif;
    color: var(--secondary);
    margin-bottom: 1rem;
}

.card-body p {
    margin-bottom: 1.5rem;
    color: #64748b;
}

.empty-card {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
}

.no-margin {
    margin: 0;
}

.muted {
    opacity: 0.9;
}
</style>
@endsection

@section('content')
    <div class="eventos-header">
        <h1>Mis Eventos</h1>
        <p class="muted no-margin">Próximos conciertos y espectáculos en el Roig Arena.</p>
    </div>

    <section class="grid">
        @forelse ($miseventos as $evento)
                <a href="{{ route('mi-evento.info', ['id' => $evento->id], false) }}" class="event-card">
                    <img src="{{ $evento->poster_url }}" alt="{{ $evento->nombre }}" class="event-card-image">
                    <div class="event-card-body">
                        <h2 class="event-card-title">{{ $evento->nombre }}</h2>
                        <p class="event-meta">
                            <strong>{{ optional($evento->fecha)->format('d/m/Y') }}</strong>
                            @if($evento->hora) · {{ optional($evento->hora)->format('H:i') }} @endif
                        </p>
                    </div>
                </a>
        @empty
            <article class="card empty-card">
                <p class="no-margin">No hay eventos disponibles.</p>
            </article>
        @endforelse
    </section>

    <section class="card">
        <div class="card-body">
            <h2>¿Quieres ver todas las entradas?</h2>
            <p>Haz clic en el botón de abajo para ver las entradas de cada evento, incluyendo los asientos reservados.</p>
            <a href="{{ route('mis-eventos.info', [], false) }}" class="btn btn-primary">Ver Todas las Entradas</a>
        </div>
    </section>
@endsection
