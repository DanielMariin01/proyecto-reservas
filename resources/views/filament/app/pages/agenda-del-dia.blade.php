<x-filament-panels::page>
    @php
        $negocio = \App\Models\Negocio::actual();
        $fechaMostrada = \Carbon\CarbonImmutable::parse($fecha, $negocio->zona_horaria);
    @endphp

    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <x-filament::icon-button icon="heroicon-o-chevron-left" label="Día anterior" wire:click="diaAnterior" />
            <span class="text-base font-semibold capitalize">{{ \App\Support\Fecha::formatear($fechaMostrada, $negocio->zona_horaria) }}</span>
            <x-filament::icon-button icon="heroicon-o-chevron-right" label="Día siguiente" wire:click="diaSiguiente" />
        </div>

        <x-filament::button color="gray" size="sm" wire:click="irAHoy">
            Hoy
        </x-filament::button>
    </div>

    @php $columnas = $this->columnas(); @endphp

    @if ($columnas->isEmpty())
        <x-filament::empty-state
            icon="heroicon-o-users"
            heading="Aún no hay profesionales activos"
            description="Crea un profesional en el Catálogo para que aparezca aquí."
        />
    @else
        <div class="grid gap-4 overflow-x-auto" style="grid-template-columns: repeat({{ $columnas->count() }}, minmax(220px, 1fr));">
            @foreach ($columnas as $columna)
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $columna['profesional']->color ?? '#673DE6' }}"></span>
                        <span class="text-sm font-semibold">{{ $columna['profesional']->nombre }}</span>
                    </div>

                    @if ($columna['citas']->isEmpty())
                        <p class="text-sm text-gray-500">Sin citas este día.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($columna['citas'] as $cita)
                                <li
                                    class="rounded-tarjeta border-l-4 bg-white p-3 shadow-sm dark:bg-white/5"
                                    style="border-left-color: {{ $columna['profesional']->color ?? '#673DE6' }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold">{{ \App\Support\Hora::formatear($cita->inicio, $negocio->zona_horaria) }}</span>
                                        <x-filament::badge :color="$cita->estado->getColor()">
                                            {{ $cita->estado->getLabel() }}
                                        </x-filament::badge>
                                    </div>
                                    <p class="mt-1 text-sm">{{ $cita->cliente->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $cita->servicio->nombre }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
