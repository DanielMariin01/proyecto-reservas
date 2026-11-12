@php
    $negocioSuplantado = session('negocio_suplantado_id') ? \App\Models\Negocio::find(session('negocio_suplantado_id')) : null;
@endphp

@if ($negocioSuplantado)
    <div class="flex items-center justify-between bg-primario px-4 py-2 text-sm font-semibold text-blanco">
        <span>Estás viendo el negocio {{ $negocioSuplantado->nombre }} como super admin</span>
        <a href="{{ route('suplantacion.salir') }}" class="underline">Salir</a>
    </div>
@endif
