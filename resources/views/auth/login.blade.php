@extends('layouts.app')

@section('title', 'Iniciar Sesión - Mi Arena')

@section('content')

<div class="hero">
    <h1>ACCEDE A TU CUENTA</h1>
    <p>Inicia sesión para reservar tus entradas</p>
</div>

<div style="max-width: 500px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        
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
                placeholder="••••••••"
            >
            @error('password')
                <span style="color: var(--danger); font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Iniciar Sesión
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #ddd;">
        <p>¿No tienes cuenta? <a href="{{ route('register') }}" style="color: var(--primary); font-weight: bold;">Regístrate aquí</a></p>
    </div>

</div>

@endsection
