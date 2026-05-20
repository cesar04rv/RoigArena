@extends('layouts.app')

@section('title', 'Registro - Mi Arena')

@section('content')

<div class="hero">
    <h1>CREA TU CUENTA</h1>
    <p>Únete a Cesar Arena y reserva tus entradas</p>
</div>

<div style="max-width: 600px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label" for="nombre">Nombre</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    class="form-control" 
                    required 
                    value="{{ old('nombre') }}"
                    placeholder="César"
                >
                @error('nombre')
                    <span style="color: var(--danger); font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="apellido">Apellido</label>
                <input 
                    type="text" 
                    id="apellido" 
                    name="apellido" 
                    class="form-control" 
                    required 
                    value="{{ old('apellido') }}"
                    placeholder="Rodríguez"
                >
                @error('apellido')
                    <span style="color: var(--danger); font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control" 
                required 
                value="{{ old('email') }}"
                placeholder="tu@email.com"
            >
            @error('email')
                <span style="color: var(--danger); font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Contraseña</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control" 
                required 
                minlength="8"
                placeholder="Mínimo 8 caracteres"
            >
            @error('password')
                <span style="color: var(--danger); font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirmar Contraseña</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="form-control" 
                required 
                placeholder="Repite tu contraseña"
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Crear Cuenta
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #ddd;">
        <p>¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: bold;">Inicia sesión aquí</a></p>
    </div>

</div>

@endsection
