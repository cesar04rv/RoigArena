// ===========================
// MI ARENA - JavaScript Simple
// Carrito y Sistema de Reservas
// VERSIÓN SESIÓN WEB (sin tokens)
// ===========================

// === CONFIGURACIÓN ===
const API_URL = '/api';
let carrito = [];

// === UTILIDADES ===
function getHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
    };
}

async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: getHeaders(),
        credentials: 'same-origin' // IMPORTANTE: envía cookies de sesión
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(`${API_URL}${endpoint}`, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || result.error || 'Error en la petición');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

function mostrarAlerta(mensaje, tipo = 'info') {
    // Crear contenedor de toasts si no existe
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 350px;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Crear toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    
    // Colores según tipo
    const colores = {
        success: 'background: #10b981; color: white;',
        danger: 'background: #dc2626; color: white;',
        warning: 'background: #f59e0b; color: white;',
        info: 'background: #1e293b; color: white;'
    };
    
    toast.style.cssText = `
        ${colores[tipo] || colores.info}
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        font-size: 0.9rem;
        font-weight: 600;
        animation: slideInRight 0.3s ease;
        cursor: pointer;
    `;
    
    toast.textContent = mensaje;
    
    // Cerrar al hacer clic
    toast.onclick = () => toast.remove();
    
    toastContainer.appendChild(toast);
    
    // Auto-eliminar después de 4 segundos
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// === CARRITO ===
// === CARRITO ===
function actualizarCarrito() {
    const carritoItems = document.getElementById('carrito-items');
    const carritoTotal = document.getElementById('carrito-total');
    const carritoCount = document.getElementById('carrito-count');
    
    if (!carritoItems) return;
    
    carritoItems.innerHTML = '';
    let total = 0;
    
    // Si hay elementos en el carrito, iniciar contador global
    if (carrito.length > 0 && !contadorGlobal) {
        iniciarContadorGlobal();
    } else if (carrito.length === 0 && contadorGlobal) {
        detenerContadorGlobal();
    }
    
    carrito.forEach((item, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'carrito-item';
        itemDiv.innerHTML = `
            <div>
                <strong>${item.sector}</strong><br>
                Fila ${item.fila} - Asiento ${item.numero}<br>
                <span style="color: var(--primary);">${item.precio}€</span>
            </div>
            <button onclick="eliminarDelCarrito(${index})" class="btn btn-danger btn-sm">X</button>
        `;
        carritoItems.appendChild(itemDiv);
        total += parseFloat(item.precio);
    });
    
    // Añadir contador global si hay items
    if (carrito.length > 0) {
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
        carritoItems.appendChild(contadorDiv);
        actualizarContadorGlobal();
    }
    
    if (carritoTotal) {
        carritoTotal.textContent = `Total: ${total.toFixed(2)}€`;
    }
    
    if (carritoCount) {
        carritoCount.textContent = carrito.length;
        carritoCount.style.display = carrito.length > 0 ? 'inline' : 'none';
    }
}

async function eliminarDelCarrito(index) {
    const item = carrito[index];
    
    if (item.reserva_id) {
        try {
            await apiCall(`/reservas/${item.reserva_id}`, 'DELETE');
            console.log('✅ Reserva eliminada del servidor');
        } catch (error) {
            console.error('Error al eliminar reserva:', error);
        }
    }
    
    carrito.splice(index, 1);
    actualizarCarrito();
    actualizarVistaAsientos();

    // Recargar asientos si estamos en la página de compra
    if (typeof cargarTodosLosAsientosDelEvento === 'function') {
        await cargarTodosLosAsientosDelEvento();
    }
    
    mostrarAlerta('Asiento eliminado del carrito', 'info');
}

function toggleCarrito() {
    const carritoContainer = document.getElementById('carrito-container');
    if (carritoContainer) {
        carritoContainer.classList.toggle('activo');
    }
}

async function vaciarCarrito() {
    if (confirm('¿Seguro que quieres vaciar el carrito?')) {
        await liberarTodasReservas();
     
        // Recargar asientos si estamos en la página de compra
        if (typeof cargarTodosLosAsientosDelEvento === 'function') {
            await cargarTodosLosAsientosDelEvento();
        }
        
        mostrarAlerta('Carrito vaciado', 'info');
    }
}

async function procesarCompra() {
    if (carrito.length === 0) {
        mostrarAlerta('El carrito está vacío', 'warning');
        return;
    }
    
    console.log('🛒 Procesando compra de', carrito.length, 'asientos');
    
    try {
        const reservasIds = carrito.map(item => item.reserva_id).filter(Boolean);
        
        if (reservasIds.length === 0) {
            mostrarAlerta('No hay reservas válidas', 'warning');
            return;
        }
        
        const compra = await apiCall('/compras', 'POST', {
            reservas: reservasIds
        });
        
        console.log('✅ Compra completada:', compra);
        
        mostrarAlerta('¡Compra realizada con éxito!', 'success');
        carrito = [];
        actualizarCarrito();
        
        setTimeout(() => {
            window.location.href = '/mis-entradas';
        }, 2000);
        
    } catch (error) {
        console.error('❌ Error en compra:', error);
        mostrarAlerta('Error al procesar la compra: ' + error.message, 'danger');
    }
}

// === EVENTOS ===
async function cargarEventos() {
    try {
        const response = await apiCall('/eventos');
        const eventos = response.data;
        
        const eventosGrid = document.getElementById('eventos-grid');
        if (!eventosGrid) return;
        
        eventosGrid.innerHTML = '';
        
        eventos.forEach(evento => {
            const eventoCard = document.createElement('div');
            eventoCard.className = 'evento-card';
            eventoCard.innerHTML = `
                <img src="${evento.poster_url || '/images/default-event.jpg'}" alt="${evento.nombre}" class="evento-img">
                <div class="evento-body">
                    <h3 class="evento-titulo">${evento.nombre}</h3>
                    <p class="evento-fecha">${formatearFecha(evento.fecha)} - ${evento.hora}</p>
                    <p class="evento-desc">${evento.descripcion_corta || ''}</p>
                    <a href="/eventos/${evento.id}" class="btn btn-primary btn-block">Ver Detalles</a>
                </div>
            `;
            eventosGrid.appendChild(eventoCard);
        });
        
    } catch (error) {
        console.error('Error cargando eventos:', error);
    }
}

function formatearFecha(fecha) {
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

// === SISTEMA DE ASIENTOS ===
let eventoActual = null;
let sectorActual = null;

async function cargarSectores(eventoId) {
    try {
        const response = await apiCall(`/eventos/${eventoId}`);
        eventoActual = response.data;
        
        const sectoresContainer = document.getElementById('sectores-container');
        if (!sectoresContainer) return;
        
        sectoresContainer.innerHTML = '';
        
        eventoActual.sectores.forEach(sector => {
            const sectorDiv = document.createElement('div');
            sectorDiv.className = 'sector-btn';
            sectorDiv.onclick = () => seleccionarSector(sector);
            sectorDiv.innerHTML = `
                <div class="sector-nombre">${sector.nombre}</div>
                <div class="sector-info">Capacidad: ${sector.capacidad}</div>
                <div class="sector-precio">${sector.precio}€</div>
            `;
            sectoresContainer.appendChild(sectorDiv);
        });
        
    } catch (error) {
        console.error('Error cargando sectores:', error);
    }
}

async function seleccionarSector(sector) {
    sectorActual = sector;
    
    document.querySelectorAll('.sector-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    
    await cargarAsientos(eventoActual.id, sector.id);
}

async function cargarAsientos(eventoId, sectorId) {
    console.log('🔍 Cargando asientos para evento:', eventoId, 'sector:', sectorId);
    
    try {
        const response = await apiCall(`/eventos/${eventoId}/sectores/${sectorId}/asientos`);
        const asientos = response.data;
        
        console.log('📊 Asientos recibidos:', asientos.length);
        
        const asientosGrid = document.getElementById('asientos-grid');
        if (!asientosGrid) return;
        
        asientosGrid.innerHTML = '';
        
        asientos.forEach(asiento => {
            const asientoDiv = document.createElement('div');
            asientoDiv.dataset.asientoId = asiento.id;
            
            const enCarrito = carrito.some(item => item.id === asiento.id);
            let clase = asiento.estado;
            
            if (enCarrito && asiento.estado === 'disponible') {
                clase = 'seleccionado';
            }
            
            asientoDiv.className = `asiento ${clase}`;
            asientoDiv.innerHTML = `
                <div>${asiento.fila}</div>
                <div>${asiento.numero}</div>
            `;
            
            if (asiento.estado === 'disponible' && !enCarrito) {
                asientoDiv.style.cursor = 'pointer';
                asientoDiv.onclick = () => seleccionarAsiento(asiento);
            } else {
                asientoDiv.style.cursor = 'not-allowed';
            }
            
            asientosGrid.appendChild(asientoDiv);
        });
        
    } catch (error) {
        console.error('❌ Error cargando asientos:', error);
        mostrarAlerta('Error al cargar los asientos', 'danger');
    }
}

async function seleccionarAsiento(asiento) {
    console.log('🎯 Seleccionando asiento:', asiento.id);
    
    try {
        const response = await apiCall('/reservas', 'POST', {
            evento_id: eventoActual.id,
            asiento_id: asiento.id
        });
        
        console.log('✅ Reserva creada:', response);
        
        const asientoData = {
            id: asiento.id,
            reserva_id: response.data.id,
            evento_id: eventoActual.id,
            asiento_id: asiento.id,
            sector: sectorActual.nombre,
            fila: asiento.fila,
            numero: asiento.numero,
            precio: sectorActual.precio,
            expira: response.data.reservado_hasta
        };
        
        carrito.push(asientoData);
        actualizarCarrito();
        
        const asientoDiv = document.querySelector(`[data-asiento-id="${asiento.id}"]`);
        if (asientoDiv) {
            asientoDiv.classList.remove('disponible');
            asientoDiv.classList.add('seleccionado');
            asientoDiv.style.cursor = 'not-allowed';
            asientoDiv.onclick = null;
        }
        
        mostrarAlerta('Asiento reservado por 5 minutos', 'success');
        
    } catch (error) {
        console.error('❌ Error:', error);
        if (error.message.includes('401') || error.message.includes('Unauthenticated')) {
            mostrarAlerta('Debes iniciar sesión para reservar', 'warning');
            setTimeout(() => window.location.href = '/login', 1500);
        } else {
            mostrarAlerta(error.message || 'Error al reservar', 'danger');
        }
    }
}

// === SINCRONIZACIÓN ===
async function sincronizarCarrito() {
    console.log('🔄 Sincronizando carrito...');
    
    try {
        const response = await fetch(`${API_URL}/reservas`, {
            method: 'GET',
            headers: getHeaders(),
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                console.log('Usuario no autenticado');
                carrito = [];
                actualizarCarrito();
                return;
            }
            throw new Error('Error al sincronizar');
        }
        
        const reservas = await response.json();
        console.log('📋 Reservas del servidor:', reservas);
        
        const reservasArray = Array.isArray(reservas) ? reservas : (reservas.data || []);
        
        carrito = reservasArray.map(reserva => {
            // Extraer el precio del campo "precio" del resource (ej: "75,00 €")
            let precioNumerico = 0;
            if (reserva.precio) {
                // Quitar el símbolo € y convertir coma a punto
                const precioStr = reserva.precio.replace('€', '').replace(',', '.').trim();
                precioNumerico = parseFloat(precioStr) || 0;
            }
            
            return {
                id: reserva.asiento_id,
                reserva_id: reserva.id,
                evento_id: reserva.evento_id,
                asiento_id: reserva.asiento_id,
                sector: reserva.asiento?.sector?.nombre || reserva.asiento?.nombre?.split(' - ')[0] || 'Sector',
                fila: reserva.asiento?.fila || '?',
                numero: reserva.asiento?.numero || '?',
                precio: precioNumerico,
                expira: reserva.reservado_hasta
            };
        });
        
        console.log('✅ Carrito sincronizado:', carrito.length, 'items');
        console.log('Precios:', carrito.map(i => i.precio));
        actualizarCarrito();
        actualizarVistaAsientos();
        
        // Disparar evento para que buy.blade.php actualice su carrito
        document.dispatchEvent(new Event('carrito-sincronizado'));
        
    } catch (error) {
        console.error('Error en sincronización:', error);
    }
}

function actualizarVistaAsientos() {
    if (!document.getElementById('asientos-grid')) return;
    
    // Obtener el evento actual de la página
    const eventoIdMeta = document.querySelector('meta[name="evento-id"]');
    const eventoActualId = eventoIdMeta ? parseInt(eventoIdMeta.content) : null;
    
    document.querySelectorAll('.asiento').forEach(asientoDiv => {
        const asientoId = parseInt(asientoDiv.dataset.asientoId);
        if (!asientoId) return;
        
        // FILTRAR: solo marcar si el asiento está en el carrito Y es del evento actual
        const enCarrito = carrito.some(item => 
            item.id === asientoId && 
            (!eventoActualId || item.evento_id === eventoActualId)
        );
        
        if (enCarrito) {
            asientoDiv.classList.remove('disponible');
            asientoDiv.classList.add('seleccionado');
            asientoDiv.style.cursor = 'not-allowed';
            asientoDiv.onclick = null;
        }
    });
}

async function liberarTodasReservas() {
    if (carrito.length === 0) return;
    
    console.log('🔓 Liberando reservas...');
    
    for (const item of carrito) {
        if (item.reserva_id) {
            try {
                await fetch(`${API_URL}/reservas/${item.reserva_id}`, {
                    method: 'DELETE',
                    headers: getHeaders(),
                    credentials: 'same-origin'
                });
            } catch (error) {
                console.error('Error liberando:', error);
            }
        }
    }
    
    carrito = [];
    actualizarCarrito();
}

// === CONTADOR ===
let contadorInterval = null;

// === CONTADOR GLOBAL ===
let contadorGlobal = null;
let tiempoRestanteGlobal = 5 * 60; // 5 minutos en segundos

function iniciarContadorGlobal() {
    // Si ya hay un contador, no reiniciarlo
    if (contadorGlobal) return;
    
    tiempoRestanteGlobal = 5 * 60;
    
    contadorGlobal = setInterval(() => {
        tiempoRestanteGlobal--;
        
        if (tiempoRestanteGlobal <= 0) {
            clearInterval(contadorGlobal);
            contadorGlobal = null;
            
            // Liberar todas las reservas
            mostrarAlerta('⏰ Tiempo agotado. Tus reservas han expirado.', 'warning');
            liberarTodasReservas();
            
            // Recargar asientos si estamos en página de compra
            if (typeof cargarTodosLosAsientosDelEvento === 'function') {
                cargarTodosLosAsientosDelEvento();
            }
        }
        
        actualizarContadorGlobal();
    }, 1000);
}

function detenerContadorGlobal() {
    if (contadorGlobal) {
        clearInterval(contadorGlobal);
        contadorGlobal = null;
    }
}

function actualizarContadorGlobal() {
    // Actualizar en el carrito lateral (si existe)
    const contadorDiv = document.getElementById('contador-global');
    if (contadorDiv && tiempoRestanteGlobal > 0) {
        const minutos = Math.floor(tiempoRestanteGlobal / 60);
        const segundos = tiempoRestanteGlobal % 60;
        contadorDiv.textContent = `⏱ ${minutos}:${segundos.toString().padStart(2, '0')} para completar tu compra`;
        
        // Cambiar color si quedan menos de 1 minuto
        if (tiempoRestanteGlobal < 60) {
            contadorDiv.style.color = '#dc2626';
            contadorDiv.style.fontWeight = '700';
        }
    }
}

// === INICIALIZACIÓN ===
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Iniciando aplicación...');
    
    sincronizarCarrito();
    setInterval(sincronizarCarrito, 10000);
    
    if (document.getElementById('eventos-grid')) {
        cargarEventos();
    }
    
    const eventoIdMeta = document.querySelector('meta[name="evento-id"]');
    if (eventoIdMeta) {
        cargarSectores(eventoIdMeta.content);
    }
    
    // === ESTILOS DE ANIMACIÓN PARA TOASTS ===
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        #toast-container .toast:hover {
            opacity: 0.9;
        }
    `;
    document.head.appendChild(style);
});