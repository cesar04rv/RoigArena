@extends('layouts.app')

@section('title', 'Inicio - Mi Arena')

@section('content')

<!-- HERO PRINCIPAL -->
<div class="hero" style="padding: 3rem 2rem;">
    <h1 style="font-size: 2.8rem; margin-bottom: 1rem;">⚡ BIENVENIDO A MI ARENA ⚡</h1>
    <p style="font-size: 1.2rem; margin-bottom: 2rem;">
        Los mejores eventos deportivos y espectáculos en un solo lugar
    </p>
    <a href="{{ route('eventos.index', [], false) }}" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.9rem 2rem;">
         Ver Eventos Disponibles
    </a>
</div>

<!-- CARACTERÍSTICAS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin: 2.5rem 0; max-width: 1000px; margin-left: auto; margin-right: auto;">
    
    <div style="background: white; padding: 1.8rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
        <h3 style="color: var(--secondary); margin-bottom: 0.8rem; font-size: 1.2rem;">Reserva Fácil</h3>
        <p style="color: #666; line-height: 1.5;">Selecciona tu asiento en un grid visual simple e intuitivo</p>
    </div>

    <div style="background: white; padding: 1.8rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">🛒</div>
        <h3 style="color: var(--secondary); margin-bottom: 0.8rem; font-size: 1.2rem;">Carrito Simple</h3>
        <p style="color: #666; line-height: 1.5;">Añade múltiples asientos y confirma tu compra de forma rápida</p>
    </div>

    <div style="background: white; padding: 1.8rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎫</div>
        <h3 style="color: var(--secondary); margin-bottom: 0.8rem; font-size: 1.2rem;">Entradas Digitales</h3>
        <p style="color: #666; line-height: 1.5;">Recibe tus entradas con código QR instantáneamente</p>
    </div>

</div>

<!-- PRÓXIMOS EVENTOS DESTACADOS -->
<div style="margin-top: 3rem;">
    <h2 style="text-align: center; font-family: var(--font-display); font-size: 2rem; margin-bottom: 1.5rem; color: var(--secondary);">
         PRÓXIMOS EVENTOS
    </h2>
    <div id="eventos-grid" class="eventos-grid">
        <div class="loader"></div>
    </div>
</div>

<!-- CALL TO ACTION -->
@guest
<div style="background: linear-gradient(135deg, var(--secondary) 0%, var(--bg-dark) 100%); color: white; padding: 2.5rem; margin: 3rem auto; border-radius: 12px; text-align: center; max-width: 800px;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1rem;">¿Listo para vivir la experiencia?</h2>
    <p style="font-size: 1.1rem; margin-bottom: 1.5rem; opacity: 0.95;">Crea tu cuenta y consigue tus entradas ahora</p>
    <a href="{{ route('register', [], false) }}" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.9rem 2rem;">
        Crear Cuenta Gratis
    </a>
</div>
@endguest

@endsection

@section('page_scripts')
<script>
// Los eventos se cargan automáticamente con arena.js
</script>
@endsection
