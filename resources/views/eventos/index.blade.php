@extends('layouts.app')

@section('title', 'Eventos - Mi Arena')

@section('content')

<!-- HERO SECTION -->
<div class="hero">
    <h1>PRÓXIMOS EVENTOS</h1>
    <p>Consigue tus entradas para los mejores eventos deportivos</p>
</div>

<!-- GRID DE EVENTOS -->
<div id="eventos-grid" class="eventos-grid">
    <div class="loader"></div>
</div>

@endsection

@section('page_scripts')
<script>
// Los eventos se cargan automáticamente con arena.js
</script>
@endsection
