<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Arena - Entradas Deportivas')</title>
    <link rel="stylesheet" href="/css/estilos-v2.css?v=2">
    @yield('page_styles')
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-container">
            <div class="logo">CESAR ARENA</div>
            <nav class="nav">
                @auth
                    @if(auth()->user()->is_admin)
                        {{-- NAV ADMIN: solo crear y cancelaciones --}}
                        <a href="{{ route('admin.eventos.create') }}" style="background: #10b981; padding: 0.5rem 1rem; border-radius: 6px; color: white;">
                            Crear Evento
                        </a>
                        <a href="{{ route('admin.cancelaciones.index') }}" style="background: #ef4444; padding: 0.5rem 1rem; border-radius: 6px; color: white; position:relative;">
                            Cancelaciones
                            @php $pendientes = \App\Models\SolicitudCancelacion::pendientes()->count(); @endphp
                            @if($pendientes > 0)
                                <span style="position:absolute;top:-6px;right:-6px;background:#fbbf24;color:#1e293b;border-radius:50%;width:18px;height:18px;font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $pendientes }}</span>
                            @endif
                        </a>
                        <form method="POST" action="{{ route('logout.post') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: var(--text-light); cursor: pointer; padding: 0.5rem 1rem; font: inherit;">
                                Salir
                            </button>
                        </form>
                        <span style="color: var(--accent);">{{ auth()->user()->nombre }}</span>
                    @else
                        {{-- NAV USUARIO NORMAL --}}
                        <a href="{{ route('home', [], false) }}">Inicio</a>
                        <a href="{{ route('eventos.index', [], false) }}">Eventos</a>
                        <a href="{{ route('mis-eventos') }}">Mis Eventos</a>
                        <form method="POST" action="{{ route('logout.post') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: var(--text-light); cursor: pointer; padding: 0.5rem 1rem; font: inherit;">
                                Salir
                            </button>
                        </form>
                        <span style="color: var(--accent);">{{ auth()->user()->nombre }}</span>
                        
                    @endif
                @else
                    {{-- NAV VISITANTE --}}
                    <a href="{{ route('home', [], false) }}">Inicio</a>
                    <a href="{{ route('eventos.index', [], false) }}">Eventos</a>
                    <a href="{{ route('login', [], false) }}">Acceder</a>
                    <a href="{{ route('register', [], false) }}">Registro</a>
                    <button onclick="toggleCarrito()" class="btn btn-cart">
                        Carrito <span id="carrito-count" style="display:none;"></span>
                    </button>
                @endauth
            </nav>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main>
        <div class="container">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    {{ $message }}
                </div>
            @endif
            @if ($message = Session::get('error'))
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="/js/arena.js"></script>
    @yield('page_scripts')
</body>
</html>
