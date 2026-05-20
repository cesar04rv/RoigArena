@extends('layouts.app')

@section('title', 'Comprar Entradas - ' . $evento->nombre)

@section('page_styles')
<style>
.compra-container {
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.compra-header {
    background: linear-gradient(135deg, var(--secondary) 0%, #0f172a 100%);
    color: white;
    padding: 2rem;
    text-align: center;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.compra-header h1 {
    font-family: Impact, sans-serif;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.compra-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .compra-layout {
        grid-template-columns: 1fr;
    }
}

/* ÁREA DE ASIENTOS */
.asientos-area {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.asientos-area h2 {
    font-family: Impact, sans-serif;
    color: var(--secondary);
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
}

/* LEYENDA */
.legend {
    display: flex;
    gap: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
    border: 2px solid var(--border);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.9rem;
}

.legend-color {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 2px solid;
}

.legend .seat-disponible {
    background: #d1fae5;
    border-color: #10b981;
}

.legend .seat-ocupado {
    background: #fee2e2;
    border-color: #dc2626;
}

.legend .seat-selected {
    background: var(--primary);
    border-color: var(--primary);
}

/* ESCENARIO */
.escenario {
    background: linear-gradient(135deg, var(--primary) 0%, #b91c1c 100%);
    color: white;
    text-align: center;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    font-weight: 700;
    font-size: 1.2rem;
    letter-spacing: 2px;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* GRID DE ASIENTOS */
.asientos-grid {
    display: grid;
    grid-template-columns: repeat(20, 1fr);
    gap: 6px;
    padding: 1rem;
    background: #1e293b;
    border-radius: 8px;
    justify-items: center;
}

.asiento {
    width: 100%;
    aspect-ratio: 1;
    max-width: 40px;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 600;
    transition: all 0.2s ease;
    border: 2px solid;
    position: relative;
}

.asiento > div:first-child {
    font-size: 0.7rem;
    font-weight: 700;
}

.asiento > div:last-child {
    font-size: 0.6rem;
}

.asiento.disponible {
    background: #d1fae5;
    border-color: #10b981;
    color: #064e3b;
    cursor: pointer;
}

.asiento.disponible:hover {
    background: #a7f3d0;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.asiento.ocupado {
    background: #fee2e2;
    border-color: #dc2626;
    color: #7f1d1d;
    cursor: not-allowed;
    opacity: 0.6;
}

.asiento.seleccionado {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
    box-shadow: 0 4px 8px rgba(220, 38, 38, 0.4);
    transform: scale(1.05);
}

/* CARRITO LATERAL */
.carrito-lateral {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    position: sticky;
    top: 2rem;
}

.carrito-lateral h3 {
    font-family: Impact, sans-serif;
    color: var(--secondary);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.carrito-count {
    display: inline-block;
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-left: 0.5rem;
}

.carrito-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 1rem;
}

.carrito-item {
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid var(--border);
}

.carrito-item-info {
    flex: 1;
}

.carrito-item-sector {
    font-weight: 700;
    color: var(--secondary);
    margin-bottom: 0.25rem;
}

.carrito-item-asiento {
    font-size: 0.85rem;
    color: #64748b;
}

.carrito-item-precio {
    color: var(--primary);
    font-weight: 700;
    font-size: 1.1rem;
    margin-right: 0.5rem;
}

.carrito-empty {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}

.carrito-total {
    border-top: 2px solid var(--border);
    padding-top: 1rem;
    margin-top: 1rem;
    font-size: 1.3rem;
    font-weight: 700;
    text-align: right;
    color: var(--secondary);
}

.carrito-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .asientos-grid {
        grid-template-columns: repeat(10, 1fr);
        gap: 4px;
    }
    
    .asiento {
        max-width: 35px;
        font-size: 0.55rem;
    }
}
</style>
@endsection

@section('content')
<div class="compra-container">
    <!-- HEADER -->
    <div class="compra-header">
        <h1>{{ $evento->nombre }}</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">
            {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }} - {{ $evento->hora }}
        </p>
    </div>

    <div class="compra-layout">
        <!-- ÁREA DE ASIENTOS -->
        <div class="asientos-area">
            <h2>Selecciona tus asientos</h2>

            <!-- LEYENDA -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color seat-disponible"></div>
                    <span>Disponible</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color seat-ocupado"></div>
                    <span>Ocupado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color seat-selected"></div>
                    <span>Seleccionado</span>
                </div>
            </div>

            <!-- ESCENARIO -->
            <div class="escenario">
                ESCENARIO
            </div>

            <!-- GRID DE ASIENTOS -->
            <div id="asientos-grid" class="asientos-grid">
                <!-- Se cargan dinámicamente -->
            </div>
        </div>

        <!-- CARRITO LATERAL -->
        <div class="carrito-lateral">
            <h3>
                Tu Carrito
                <span class="carrito-count" id="carrito-count">0</span>
            </h3>

            <div id="carrito-items" class="carrito-items">
                <div class="carrito-empty">
                    Selecciona asientos para comenzar
                </div>
            </div>

            <div id="carrito-total" class="carrito-total" style="display: none;">
                Total: <span id="total-amount">0.00€</span>
            </div>

            <div class="carrito-actions">
                <button id="confirmar-btn" class="btn btn-primary btn-block" disabled onclick="confirmarCompra()">
                    Confirmar Compra
                </button>
                <button class="btn btn-secondary btn-block" onclick="vaciarCarrito()">
                    Vaciar Carrito
                </button>
                <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-secondary btn-block">
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Meta data -->
<meta name="evento-id" content="{{ $evento->id }}">

<!-- MODAL DE PAGO -->
<div id="paymentModal" class="payment-modal-overlay" style="display:none;">
    <div class="payment-modal-content">
        <div class="payment-modal-header">
            <h2>Datos de Pago</h2>
            <button onclick="cerrarModalPago()" class="btn-cerrar" style="position: absolute; right: 1rem; top: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <div class="payment-modal-body">
            <div class="form-group">
                <label class="form-label" for="cardNumber">Número de Tarjeta</label>
                <input 
                    type="text" 
                    id="cardNumber" 
                    class="form-control" 
                    placeholder="1234 5678 9012 3456"
                    maxlength="19"
                    required
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="cardName">Titular de la Tarjeta</label>
                <input 
                    type="text" 
                    id="cardName" 
                    class="form-control" 
                    placeholder="NOMBRE APELLIDO"
                    required
                >
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="cardExpiry">Fecha de Expiración</label>
                    <input 
                        type="text" 
                        id="cardExpiry" 
                        class="form-control" 
                        placeholder="MM/YY"
                        maxlength="5"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="cardCVV">CVV</label>
                    <input 
                        type="text" 
                        id="cardCVV" 
                        class="form-control" 
                        placeholder="123"
                        maxlength="4"
                        required
                    >
                </div>
            </div>
        </div>
        
        <div class="payment-modal-footer">
            <p style="font-size: 1.2rem; font-weight: 700; text-align: right; margin-bottom: 1rem;">
                Total: <span id="paymentTotal">0.00€</span>
            </p>
            <div style="display: flex; gap: 1rem;">
                <button id="cancelBtn" class="btn btn-secondary" style="flex: 1;">Cancelar</button>
                <button id="payBtn" class="btn btn-primary" style="flex: 1;">Pagar ahora</button>
            </div>
        </div>
    </div>
</div>

<style>
.payment-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.payment-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
}

.payment-modal-header {
    padding: 1.5rem;
    border-bottom: 2px solid var(--border);
    position: relative;
}

.payment-modal-header h2 {
    margin: 0;
    color: var(--secondary);
    font-family: Impact, sans-serif;
}

.payment-modal-body {
    padding: 1.5rem;
}

.payment-modal-footer {
    padding: 1.5rem;
    border-top: 2px solid var(--border);
    background: #f8fafc;
}
</style>

@endsection

@section('page_scripts')
<script>
// === VARIABLES ESPECÍFICAS DE LA PÁGINA DE COMPRA ===
const eventoId = {{ $evento->id }};

// === SOBRESCRIBIR cargarAsientos para esta página ===
async function cargarTodosLosAsientosDelEvento() {
    try {
        const response = await fetch(`/api/eventos/${eventoId}/asientos`, {
            headers: getHeaders(),
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error('Error al cargar asientos');
        
        const data = await response.json();
        const asientos = data.data.asientos || [];
        
        console.log('📊 Asientos cargados:', asientos.length);
        
        const grid = document.getElementById('asientos-grid');
        if (!grid) return;
        
        grid.innerHTML = '';
        
        asientos.forEach(asiento => {
            const div = document.createElement('div');
            
            // IMPORTANTE: Añadir data-asiento-id
            div.dataset.asientoId = asiento.id;
            
            // FILTRAR solo asientos del evento actual
            const enCarrito = carrito.some(item => 
                item.id === asiento.id && item.evento_id === eventoId
            );
            
            let clase = asiento.estado || (asiento.disponible ? 'disponible' : 'ocupado');
            
            if (enCarrito) {
                clase = 'seleccionado';
            }
            
            div.className = `asiento ${clase}`;
            div.innerHTML = `
                <div>${asiento.fila}</div>
                <div>${asiento.numero}</div>
            `;
            
            if (asiento.estado === 'disponible' && !enCarrito) {
                div.style.cursor = 'pointer';
                div.onclick = async () => {
                    try {
                        const response = await apiCall('/reservas', 'POST', {
                            evento_id: eventoId,
                            asiento_id: asiento.id
                        });
                        
                        console.log('✅ Reserva creada:', response);
                        
                        carrito.push({
                            id: asiento.id,
                            reserva_id: response.data.id,
                            evento_id: eventoId,
                            asiento_id: asiento.id,
                            sector: asiento.sector_nombre || 'Sector',
                            fila: asiento.fila,
                            numero: asiento.numero,
                            precio: parseFloat(asiento.precio || 0),
                            expira: response.data.reservado_hasta
                        });
                        
                        actualizarCarrito();
                        cargarTodosLosAsientosDelEvento();
                        mostrarAlerta('Asiento reservado por 5 minutos', 'success');
                        
                    } catch (error) {
                        if (error.message.includes('401') || error.message.includes('Unauthenticated')) {
                            mostrarAlerta('Debes iniciar sesión para reservar', 'warning');
                            setTimeout(() => window.location.href = '/login', 1500);
                        } else {
                            mostrarAlerta(error.message || 'Error al reservar', 'danger');
                        }
                        cargarTodosLosAsientosDelEvento();
                    }
                };
            } else {
                div.style.cursor = 'not-allowed';
            }
            
            grid.appendChild(div);
        });
        
        // Actualizar visualmente los asientos en el carrito
        actualizarVistaAsientos();
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('asientos-grid').innerHTML = 
            '<p style="color: red; text-align: center; grid-column: 1/-1;">Error al cargar los asientos</p>';
    }
}

// Sobrescribir actualizarCarrito para filtrar por evento actual
const actualizarCarritoOriginal = window.actualizarCarrito;

window.actualizarCarrito = function() {
    const carritoItems = document.getElementById('carrito-items');
    const carritoTotal = document.getElementById('carrito-total');
    const carritoCount = document.getElementById('carrito-count');
    const totalAmount = document.getElementById('total-amount');
    const confirmarBtn = document.getElementById('confirmar-btn');
    
    if (!carritoItems) {
        if (actualizarCarritoOriginal) actualizarCarritoOriginal();
        return;
    }
    
    const carritoEvento = carrito.filter(item => item.evento_id === eventoId);
    
    carritoItems.innerHTML = '';
    
    if (carritoEvento.length === 0) {
        carritoItems.innerHTML = '<div class="carrito-empty">Selecciona asientos para comenzar</div>';
        if (carritoTotal) carritoTotal.style.display = 'none';
        if (confirmarBtn) confirmarBtn.disabled = true;
        if (carritoCount) {
            carritoCount.textContent = '0';
            carritoCount.style.display = 'none';
        }
        return;
    }
    
    let total = 0;
    
    carritoEvento.forEach((item) => {
        const realIndex = carrito.indexOf(item);
        const precio = parseFloat(item.precio || 0);
        total += precio;
        
        const div = document.createElement('div');
        div.className = 'carrito-item';
        
        div.innerHTML = `
            <div class="carrito-item-info">
                <div class="carrito-item-sector">${item.sector || 'Sector'}</div>
                <div class="carrito-item-asiento">Fila ${item.fila} - Asiento ${item.numero}</div>
            </div>
            <div class="carrito-item-precio">${precio.toFixed(2)}€</div>
            <button onclick="eliminarDelCarrito(${realIndex})" class="btn btn-danger btn-sm">×</button>
        `;
        carritoItems.appendChild(div);
    });
    
    // AÑADIR CONTADOR GLOBAL
    if (carritoEvento.length > 0 && typeof tiempoRestanteGlobal !== 'undefined') {
        const contadorDiv = document.createElement('div');
        contadorDiv.id = 'contador-global';
        contadorDiv.style.cssText = `
            background: #fef3c7;
            color: #92400e;
            padding: 0.75rem;
            border-radius: 6px;
            text-align: center;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            font-weight: 600;
        `;
        const minutos = Math.floor(tiempoRestanteGlobal / 60);
        const segundos = tiempoRestanteGlobal % 60;
        contadorDiv.textContent = `⏱ ${minutos}:${segundos.toString().padStart(2, '0')} para completar tu compra`;
        
        if (tiempoRestanteGlobal < 60) {
            contadorDiv.style.color = '#dc2626';
            contadorDiv.style.fontWeight = '700';
        }
        
        carritoItems.appendChild(contadorDiv);
    }
    
    if (carritoTotal) carritoTotal.style.display = 'block';
    if (totalAmount) totalAmount.textContent = total.toFixed(2) + '€';
    if (confirmarBtn) confirmarBtn.disabled = false;
    if (carritoCount) {
        carritoCount.textContent = carritoEvento.length;
        carritoCount.style.display = carritoEvento.length > 0 ? 'inline' : 'none';
    }
};

// Actualizar cuando arena.js sincronice
document.addEventListener('carrito-sincronizado', () => {
    console.log('🔔 Evento carrito-sincronizado recibido');
    actualizarCarrito();
});

// === MODAL DE PAGO ===
function cerrarModalPago() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

async function confirmarCompra() {
    if (carrito.length === 0) {
        mostrarAlerta('El carrito está vacío', 'warning');
        return;
    }
    
    const total = carrito.reduce((sum, item) => sum + parseFloat(item.precio || 0), 0);
    
    const paymentTotal = document.getElementById('paymentTotal');
    if (paymentTotal) {
        paymentTotal.textContent = total.toFixed(2) + '€';
    }
    
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

async function procesarPago() {
    console.log('💳 Procesando pago...');
    
    const cardNumber = document.getElementById('cardNumber')?.value;
    const cardName = document.getElementById('cardName')?.value;
    const cardExpiry = document.getElementById('cardExpiry')?.value;
    const cardCVV = document.getElementById('cardCVV')?.value;
    
    if (!cardNumber || !cardName || !cardExpiry || !cardCVV) {
        mostrarAlerta('Por favor, completa todos los campos de la tarjeta', 'warning');
        return;
    }
    
    const cardNumberClean = cardNumber.replace(/\s/g, '');
    if (cardNumberClean.length !== 16 || !/^\d+$/.test(cardNumberClean)) {
        mostrarAlerta('El número de tarjeta debe tener 16 dígitos', 'warning');
        return;
    }
    
    if (cardCVV.length < 3 || cardCVV.length > 4 || !/^\d+$/.test(cardCVV)) {
        mostrarAlerta('El CVV debe tener 3 o 4 dígitos', 'warning');
        return;
    }
    
    try {
        const reservasIds = carrito
            .filter(item => item.reserva_id)
            .map(item => item.reserva_id);
        
        if (reservasIds.length === 0) {
            throw new Error('No hay reservas válidas en el carrito');
        }
        
        const payBtn = document.getElementById('payBtn');
        if (payBtn) {
            payBtn.disabled = true;
            payBtn.textContent = 'Procesando...';
        }
        
        const compraResponse = await apiCall('/compras', 'POST', {
            reservas: reservasIds,
            pago: {
                metodo: 'tarjeta',
                ultimos_digitos: cardNumberClean.slice(-4),
                titular: cardName
            }
        });
        
        cerrarModalPago();
        document.getElementById('cardNumber').value = '';
        document.getElementById('cardName').value = '';
        document.getElementById('cardExpiry').value = '';
        document.getElementById('cardCVV').value = '';
        
        carrito = [];
        actualizarCarrito();
        
        mostrarAlerta('¡Compra realizada con éxito!', 'success');
        
        setTimeout(() => {
            window.location.href = '/mis-eventos';
        }, 2000);
        
    } catch (error) {
        console.error('❌ Error:', error);
        mostrarAlerta('Error al procesar el pago: ' + error.message, 'danger');
        
        const payBtn = document.getElementById('payBtn');
        if (payBtn) {
            payBtn.disabled = false;
            payBtn.textContent = 'Pagar ahora';
        }
    }
}

// === INICIALIZACIÓN ===
document.addEventListener('DOMContentLoaded', () => {
    // Cargar asientos de esta página
    cargarTodosLosAsientosDelEvento();
    
    // IMPORTANTE: Esperar a que arena.js sincronice y luego actualizar
    setTimeout(() => {
        console.log('⏰ Actualizando carrito después de sincronización inicial');
        actualizarCarrito();
    }, 1000);
    
    // Auto-refresh cada 10 segundos
    setInterval(cargarTodosLosAsientosDelEvento, 10000);
    
    // Listeners del modal
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.onclick = cerrarModalPago;
    }
    
    const payBtn = document.getElementById('payBtn');
    if (payBtn) {
        payBtn.onclick = procesarPago;
    }
    
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.onclick = (e) => {
            if (e.target === modal) {
                cerrarModalPago();
            }
        };
    }
    
    // Formateo de campos
    const cardNumber = document.getElementById('cardNumber');
    if (cardNumber) {
        cardNumber.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    const cardExpiry = document.getElementById('cardExpiry');
    if (cardExpiry) {
        cardExpiry.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
    
    const cardCVV = document.getElementById('cardCVV');
    if (cardCVV) {
        cardCVV.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
        });
    }
});
</script>
@endsection