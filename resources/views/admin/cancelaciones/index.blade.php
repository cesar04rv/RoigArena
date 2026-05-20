@extends('layouts.app')

@section('title', 'Cancelaciones - Panel Admin | Roig Arena')

@section('page_styles')
<style>
/* Contadores */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-top: 4px solid #e2e8f0;
}

.stat-card.pendiente { border-top-color: var(--warning); }
.stat-card.aprobada  { border-top-color: var(--success); }
.stat-card.rechazada { border-top-color: var(--danger); }

.stat-number {
    font-family: Impact, sans-serif;
    font-size: 2.5rem;
    line-height: 1;
    margin-bottom: 0.25rem;
}
.stat-card.pendiente .stat-number { color: var(--warning); }
.stat-card.aprobada  .stat-number { color: var(--success); }
.stat-card.rechazada .stat-number { color: var(--danger); }

.stat-label {
    font-size: 0.9rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Tabs filtro */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    background: white;
    color: #64748b;
    border: 2px solid var(--border);
    transition: all 0.2s;
}

.filter-tab:hover { 
    border-color: var(--primary); 
    color: var(--primary); 
}
.filter-tab.active { 
    background: var(--primary); 
    border-color: var(--primary); 
    color: white; 
}

/* Tabla */
.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}

.table-card table {
    width: 100%;
    border-collapse: collapse;
}

.table-card th {
    background: #f8fafc;
    padding: 1rem;
    text-align: left;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    border-bottom: 2px solid var(--border);
}

.table-card td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
    font-size: 0.95rem;
}

.table-card tr:last-child td { border-bottom: none; }
.table-card tr:hover td { background: #f8fafc; }

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}
.badge-pendiente { background: #fef3c7; color: #92400e; }
.badge-aprobada  { background: #d1fae5; color: #065f46; }
.badge-rechazada { background: #fee2e2; color: #991b1b; }

.motivo-text {
    max-width: 200px;
    font-size: 0.85rem;
    color: #64748b;
    font-style: italic;
}

.actions-col {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-aprobar {
    background: var(--success);
    color: white;
    border: none;
    padding: 0.4rem 0.9rem;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}
.btn-aprobar:hover { background: #059669; }

.btn-rechazar {
    background: var(--danger);
    color: white;
    border: none;
    padding: 0.4rem 0.9rem;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}
.btn-rechazar:hover { background: var(--primary-dark); }

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state .icon { 
    font-size: 3rem; 
    margin-bottom: 1rem;
    color: var(--success);
}

/* Modal rechazo */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active { display: flex; }

.modal {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal h3 {
    font-family: Impact, sans-serif;
    color: var(--danger);
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.modal p {
    color: #64748b;
    margin-bottom: 1.25rem;
    font-size: 0.95rem;
}

.modal textarea {
    width: 100%;
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.95rem;
    resize: vertical;
    min-height: 100px;
    box-sizing: border-box;
    font-family: inherit;
    transition: border-color 0.2s;
}
.modal textarea:focus { 
    outline: none; 
    border-color: var(--primary); 
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.25rem;
    justify-content: flex-end;
}

.btn-cancelar-modal {
    background: #f1f5f9;
    color: #64748b;
    border: none;
    padding: 0.6rem 1.25rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}
.btn-cancelar-modal:hover { background: var(--border); }

/* Toast */
.toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: var(--secondary);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-size: 0.95rem;
    z-index: 9999;
    display: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.toast.show { display: block; animation: slideIn 0.3s ease; }
.toast.success { border-left: 4px solid var(--success); }
.toast.error    { border-left: 4px solid var(--danger); }

@keyframes slideIn {
    from { transform: translateX(100px); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}

@media (max-width: 768px) {
    .stats-row { grid-template-columns: 1fr; }
    .table-card { overflow-x: auto; }
}
</style>
@endsection

@section('content')

<div class="hero">
    <h1>GESTIÓN DE CANCELACIONES</h1>
    <p>Revisa y procesa las solicitudes de cancelación de los clientes</p>
</div>

{{-- Contadores --}}
<div class="stats-row">
    <div class="stat-card pendiente">
        <div class="stat-number">{{ $contadores['pendiente'] }}</div>
        <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card aprobada">
        <div class="stat-number">{{ $contadores['aprobada'] }}</div>
        <div class="stat-label">Aprobadas</div>
    </div>
    <div class="stat-card rechazada">
        <div class="stat-number">{{ $contadores['rechazada'] }}</div>
        <div class="stat-label">Rechazadas</div>
    </div>
</div>

{{-- Filtros --}}
<div class="filter-tabs">
    <a href="?estado=pendiente" class="filter-tab {{ $estado === 'pendiente' ? 'active' : '' }}">
        Pendientes ({{ $contadores['pendiente'] }})
    </a>
    <a href="?estado=aprobada" class="filter-tab {{ $estado === 'aprobada' ? 'active' : '' }}">
        Aprobadas
    </a>
    <a href="?estado=rechazada" class="filter-tab {{ $estado === 'rechazada' ? 'active' : '' }}">
        Rechazadas
    </a>
    <a href="?estado=todas" class="filter-tab {{ $estado === 'todas' ? 'active' : '' }}">
        Todas
    </a>
</div>

{{-- Tabla --}}
<div class="table-card">
    @if($solicitudes->isEmpty())
        <div class="empty-state">
            <div class="icon">✓</div>
            <strong>No hay solicitudes {{ $estado !== 'todas' ? $estado . 's' : '' }}</strong>
            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Todo está al día.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Evento</th>
                    <th>Asiento</th>
                    <th>Precio</th>
                    <th>Motivo cliente</th>
                    <th>Fecha solicitud</th>
                    <th>Estado</th>
                    @if($estado === 'pendiente' || $estado === 'todas')
                        <th>Acciones</th>
                    @endif
                    @if($estado === 'rechazada' || $estado === 'todas')
                        <th>Motivo rechazo</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($solicitudes as $sol)
                    @php
                        $entrada = $sol->entrada;
                        $asiento = $entrada?->asiento;
                        $nombreAsiento = $asiento && $asiento->sector
                            ? $asiento->sector->nombre . ' · Fila ' . $asiento->fila . ' · Nº ' . $asiento->numero
                            : '—';
                    @endphp
                    <tr id="row-{{ $sol->id }}">
                        <td style="color:#94a3b8;">#{{ $sol->id }}</td>
                        <td>
                            <strong>{{ $sol->usuario?->nombre }} {{ $sol->usuario?->apellido }}</strong><br>
                            <span style="font-size:0.8rem;color:#94a3b8;">{{ $sol->usuario?->email }}</span>
                        </td>
                        <td>{{ $entrada?->evento?->nombre ?? '—' }}<br>
                            <span style="font-size:0.8rem;color:#94a3b8;">
                                {{ $entrada?->evento?->fecha?->format('d/m/Y') }}
                            </span>
                        </td>
                        <td style="font-size:0.85rem;">{{ $nombreAsiento }}</td>
                        <td>
                            <strong>{{ $entrada ? number_format((float)$entrada->precio_pagado, 2, ',', '.') . ' €' : '—' }}</strong>
                        </td>
                        <td>
                            <span class="motivo-text">
                                {{ $sol->motivo_usuario ?: 'Sin motivo indicado' }}
                            </span>
                        </td>
                        <td style="font-size:0.85rem;color:#64748b;">
                            {{ $sol->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $sol->estado }}">{{ ucfirst($sol->estado) }}</span>
                        </td>
                        @if($estado === 'pendiente' || $estado === 'todas')
                            <td>
                                @if($sol->esPendiente())
                                    <div class="actions-col">
                                        <button
                                            class="btn-aprobar"
                                            onclick="aprobar({{ $sol->id }})"
                                        >Aprobar</button>
                                        <button
                                            class="btn-rechazar"
                                            onclick="abrirModalRechazo({{ $sol->id }})"
                                        >Rechazar</button>
                                    </div>
                                @else
                                    <span style="color:#94a3b8;font-size:0.85rem;">Procesada</span>
                                @endif
                            </td>
                        @endif
                        @if($estado === 'rechazada' || $estado === 'todas')
                            <td>
                                <span class="motivo-text" style="color:#ef4444;">
                                    {{ $sol->motivo_rechazo ?: '—' }}
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Modal rechazar --}}
<div class="modal-overlay" id="modal-rechazo">
    <div class="modal">
        <h3>Rechazar solicitud</h3>
        <p>Indica el motivo del rechazo. El cliente lo verá en su panel.</p>
        <textarea
            id="motivo-rechazo-input"
            placeholder="Ej: El evento es en menos de 48 horas y no se permiten cancelaciones..."
            maxlength="500"
        ></textarea>
        <small style="color:#94a3b8;">Máximo 500 caracteres</small>
        <div class="modal-actions">
            <button class="btn-cancelar-modal" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-rechazar" onclick="confirmarRechazo()">Confirmar rechazo</button>
        </div>
    </div>
</div>

{{-- Toast notificación --}}
<div class="toast" id="toast"></div>

@endsection

@section('page_scripts')
<script>
let solicitudIdActual = null;

function getToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

// ── APROBAR ───────────────────────────────────────────────
async function aprobar(id) {
    if (!confirm('¿Confirmas la aprobación de esta cancelación? Se liberará el asiento y se eliminará la entrada.')) return;

    try {
        const res = await fetch(`/admin/cancelaciones/${id}/aprobar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getToken(),
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            const fila = document.getElementById(`row-${id}`);
            if (fila) {
                fila.style.opacity = '0.3';
                fila.style.transition = 'opacity 0.4s';
                setTimeout(() => fila.remove(), 500);
            }
        } else {
            mostrarToast(data.message || 'Error al aprobar', 'error');
        }
    } catch (e) {
        mostrarToast('Error de conexión', 'error');
    }
}

// ── MODAL RECHAZO ─────────────────────────────────────────
function abrirModalRechazo(id) {
    solicitudIdActual = id;
    document.getElementById('motivo-rechazo-input').value = '';
    document.getElementById('modal-rechazo').classList.add('active');
}

function cerrarModal() {
    solicitudIdActual = null;
    document.getElementById('modal-rechazo').classList.remove('active');
}

async function confirmarRechazo() {
    const motivo = document.getElementById('motivo-rechazo-input').value.trim();

    if (!motivo) {
        alert('Debes indicar el motivo del rechazo.');
        return;
    }

    try {
        const res = await fetch(`/admin/cancelaciones/${solicitudIdActual}/rechazar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ motivo_rechazo: motivo }),
        });
        const data = await res.json();

        cerrarModal();

        if (data.success) {
            mostrarToast(data.message, 'success');
            const fila = document.getElementById(`row-${solicitudIdActual}`);
            if (fila) {
                fila.style.opacity = '0.3';
                fila.style.transition = 'opacity 0.4s';
                setTimeout(() => fila.remove(), 500);
            }
        } else {
            mostrarToast(data.message || 'Error al rechazar', 'error');
        }
    } catch (e) {
        mostrarToast('Error de conexión', 'error');
        cerrarModal();
    }
}

// Cerrar modal haciendo clic fuera
document.getElementById('modal-rechazo').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// ── TOAST ─────────────────────────────────────────────────
function mostrarToast(msg, tipo = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = `toast show ${tipo}`;
    setTimeout(() => { toast.className = 'toast'; }, 4000);
}
</script>
@endsection
