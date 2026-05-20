@extends('layouts.app')

@section('title', 'Mis Eventos Detallados | Roig Arena')

@section('page_styles')
    <link rel="stylesheet" href="/css/pages/eventos.css">
@endsection

@section('content')
    <div class="eventos-header">
        <h1>Mis Eventos Detallados</h1>
        <p class="muted no-margin">Aquí puedes ver tus entradas en formato lista, con los datos del evento y tu asiento.</p>
    </div>

    <section class="ticket-list grid-gap-bottom">
        @php $hayEntradas = false; @endphp

        @foreach ($miseventos as $evento)
            @foreach ($evento->entradas as $entrada)
                @php
                    $hayEntradas = true;
                    $asiento = $entrada->asiento;
                    $nombreAsiento = $asiento && $asiento->sector
                        ? $asiento->sector->nombre . ' - Fila ' . $asiento->fila . ' - Asiento ' . $asiento->numero
                        : 'Asiento no disponible';
                @endphp

                <article class="card ticket-card">
                    <div class="ticket-main">
                        <h2 class="ticket-event-name">{{ $evento->nombre }}</h2>
                        <p class="muted no-margin">
                            Fecha: {{ $evento->fecha ? $evento->fecha->format('d/m/Y') : 'Por confirmar' }}
                            · Hora: {{ $evento->hora ? $evento->hora->format('H:i') : 'Por confirmar' }}
                        </p>
                        <p class="ticket-seat no-margin">{{ $nombreAsiento }}</p>
                    </div>

                    <div class="ticket-side">
                        <p class="muted no-margin">Entrada #{{ $entrada->id }}</p>
                        <p class="muted no-margin">Precio: {{ number_format((float) $entrada->precio_pagado, 2, ',', '.') }} €</p>
                        <div class="ticket-actions">
                            <button
                                type="button"
                                class="btn btn-sm ticket-download-btn"
                                data-ticket-download
                                data-evento="{{ $evento->nombre }}"
                                data-fecha="{{ $evento->fecha ? $evento->fecha->format('d/m/Y') : 'Por confirmar' }}"
                                data-hora="{{ $evento->hora ? $evento->hora->format('H:i') : 'Por confirmar' }}"
                                data-asiento="{{ $nombreAsiento }}"
                                data-entrada="{{ $entrada->id }}"
                                data-precio="{{ number_format((float) $entrada->precio_pagado, 2, ',', '.') }} €"
                                data-codigo="{{ $entrada->codigo_qr }}"
                            >
                                Descargar PDF
                            </button>
                            @if (!$entrada->descargada)
                                <button
                                    type="button"
                                    class="btn btn-sm btn-cancelar-solicitud"
                                    data-entrada="{{ $entrada->id }}"
                                    onclick="abrirModalCancelacion({{ $entrada->id }})"
                                >
                                    Solicitar cancelación
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        @endforeach

        @if(!$hayEntradas)
            <article class="card">
                <p class="no-margin">No tienes entradas disponibles.</p>
            </article>
        @endif
    </section>
@endsection

@section('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="/js/pages/downloadpdf.js"></script>
    <style>
        .btn-cancelar-solicitud {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .btn-cancelar-solicitud:hover {
            background: #fca5a5;
        }
        .modal-cancelacion-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-cancelacion-overlay.active { display: flex; }
        .modal-cancelacion {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .modal-cancelacion h3 {
            font-family: Impact, sans-serif;
            color: #ef4444;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .modal-cancelacion p {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        .modal-cancelacion textarea {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.95rem;
            resize: vertical;
            min-height: 90px;
            box-sizing: border-box;
            font-family: inherit;
        }
        .modal-cancelacion textarea:focus { outline: none; border-color: #ef4444; }
        .modal-reembolso-info {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            color: #166534;
            margin-top: 1rem;
        }
        .modal-cancelacion-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.25rem;
            justify-content: flex-end;
        }
        .btn-modal-cancelar { background: #f1f5f9; color: #64748b; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-modal-cancelar:hover { background: #e2e8f0; }
        .btn-modal-enviar { background: #ef4444; color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-modal-enviar:hover { background: #dc2626; }
        .toast-user {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #1e293b;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            z-index: 9999;
            display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .toast-user.show { display: block; }
        .toast-user.success { border-left: 4px solid #10b981; }
        .toast-user.error   { border-left: 4px solid #ef4444; }
    </style>

    {{-- Modal solicitar cancelación --}}
    <div class="modal-cancelacion-overlay" id="modal-cancelacion">
        <div class="modal-cancelacion">
            <h3>Solicitar cancelación</h3>
            <p>Indica el motivo de tu solicitud (opcional). El administrador la revisará y te notificará la decisión.</p>
            <textarea
                id="motivo-cancelacion"
                placeholder="¿Por qué deseas cancelar? (opcional)"
                maxlength="500"
            ></textarea>
            <div class="modal-reembolso-info">
                💳 <strong>Reembolso:</strong> Si la cancelación es aprobada, recibirás el importe en un plazo de <strong>5 a 7 días hábiles</strong> en el método de pago original.
            </div>
            <div class="modal-cancelacion-actions">
                <button class="btn-modal-cancelar" onclick="cerrarModalCancelacion()">Volver</button>
                <button class="btn-modal-enviar" onclick="enviarSolicitud()">Enviar solicitud</button>
            </div>
        </div>
    </div>

    <div class="toast-user" id="toast-user"></div>

    <script>
    let entradaIdActual = null;

    function abrirModalCancelacion(entradaId) {
        entradaIdActual = entradaId;
        document.getElementById('motivo-cancelacion').value = '';
        document.getElementById('modal-cancelacion').classList.add('active');
    }

    function cerrarModalCancelacion() {
        entradaIdActual = null;
        document.getElementById('modal-cancelacion').classList.remove('active');
    }

    document.getElementById('modal-cancelacion').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalCancelacion();
    });

    async function enviarSolicitud() {
        const motivo = document.getElementById('motivo-cancelacion').value.trim();

        try {
            const token = localStorage.getItem('auth_token') || '';
            const res = await fetch(`/api/entradas/${entradaIdActual}/solicitar-cancelacion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({ motivo }),
            });

            const data = await res.json();
            cerrarModalCancelacion();

            if (data.success) {
                mostrarToastUser(data.message, 'success');
                // Deshabilitar el botón de esa entrada para evitar doble solicitud
                const btn = document.querySelector(`[data-entrada="${entradaIdActual}"].btn-cancelar-solicitud`);
                if (btn) {
                    btn.textContent = '⏳ Solicitud enviada';
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                    btn.style.cursor = 'not-allowed';
                }
            } else {
                mostrarToastUser(data.message || 'Error al enviar la solicitud', 'error');
            }
        } catch (e) {
            mostrarToastUser('Error de conexión. Inténtalo de nuevo.', 'error');
            cerrarModalCancelacion();
        }
    }

    function mostrarToastUser(msg, tipo = 'success') {
        const toast = document.getElementById('toast-user');
        toast.textContent = msg;
        toast.className = `toast-user show ${tipo}`;
        setTimeout(() => { toast.className = 'toast-user'; }, 5000);
    }
    </script>
@endsection
