@extends('layouts.app')

@section('title', 'Mis Entradas - Mi Arena')

@section('page_styles')
<style>
.hero {
    background: linear-gradient(135deg, var(--secondary) 0%, #0f172a 100%);
    color: white;
    text-align: center;
    padding: 3rem 1rem;
    margin-bottom: 2rem;
    border-radius: 12px;
}

.hero h1 {
    font-family: Impact, sans-serif;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.hero p {
    font-size: 1.1rem;
    opacity: 0.9;
}

#entradas-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (max-width: 768px) {
    #entradas-container {
        grid-template-columns: 1fr;
    }
}

.entrada-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.entrada-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.entrada-header {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.entrada-header h3 {
    font-family: Impact, sans-serif;
    color: var(--primary);
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
}

.entrada-info {
    margin-bottom: 1rem;
}

.entrada-info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.entrada-info-item strong {
    color: var(--secondary);
    min-width: 80px;
}

.entrada-qr {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    border: 2px dashed var(--border);
}

.entrada-qr-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.entrada-qr-code {
    font-family: monospace;
    font-size: 0.85rem;
    color: #64748b;
    word-break: break-all;
}

.loader {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    color: var(--primary);
    font-size: 1.2rem;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.empty-state h3 {
    color: var(--secondary);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}
</style>
@endsection

@section('content')

<div class="hero">
    <h1>MIS ENTRADAS</h1>
    <p>Todas tus entradas compradas</p>
</div>

<div id="entradas-container">
    <div class="loader">Cargando tus entradas...</div>
</div>

@endsection

@section('page_scripts')
<script>
async function cargarMisEntradas() {
    try {
        const response = await apiCall('/entradas');
        const entradas = response.data;
        
        const container = document.getElementById('entradas-container');
        container.innerHTML = '';
        
        if (entradas.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No tienes entradas compradas</h3>
                    <p style="margin: 1rem 0; color: #64748b;">Explora nuestros eventos y consigue tus entradas</p>
                    <a href="/eventos" class="btn btn-primary">Ver Eventos</a>
                </div>
            `;
            return;
        }
        
        entradas.forEach(entrada => {
            const entradaCard = document.createElement('div');
            entradaCard.className = 'entrada-card';
            
            entradaCard.innerHTML = `
                <div class="entrada-header">
                    <h3>${entrada.evento.nombre}</h3>
                    <div style="font-size: 0.9rem; color: #64748b;">
                        ${entrada.evento.fecha} - ${entrada.evento.hora}
                    </div>
                </div>
                
                <div class="entrada-info">
                    <div class="entrada-info-item">
                        <strong>Sector:</strong> ${entrada.sector.nombre}
                    </div>
                    <div class="entrada-info-item">
                        <strong>Asiento:</strong> Fila ${entrada.asiento.fila} - Nº ${entrada.asiento.numero}
                    </div>
                    <div class="entrada-info-item">
                        <strong>Precio:</strong> ${entrada.precio}€
                    </div>
                </div>
                
                <div class="entrada-qr">
                    <div class="entrada-qr-icon">📱</div>
                    <div class="entrada-qr-code">${entrada.codigo_qr}</div>
                </div>
            `;
            
            container.appendChild(entradaCard);
        });
        
    } catch (error) {
        const container = document.getElementById('entradas-container');
        container.innerHTML = `
            <div class="empty-state">
                <div class="alert alert-danger" style="margin: 0;">
                    Error al cargar las entradas. Por favor, inicia sesión.
                </div>
            </div>
        `;
    }
}

// Cargar entradas al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    cargarMisEntradas();
});
</script>
@endsection
