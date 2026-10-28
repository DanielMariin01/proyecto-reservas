<x-filament-panels::page>
    @php
        $negocio = \App\Models\Negocio::actual();
        $citas = $this->citasDeHoy();
    @endphp

    @if ($citas->isEmpty())
        <x-filament::empty-state
            icon="heroicon-o-calendar"
            heading="Aún no tienes citas hoy"
            description="Cuando te reserven una hora hoy, aparecerá aquí."
        />
    @else
        <div class="space-y-4">
            @foreach ($citas as $cita)
                @php $estado = $cita->obtenerEstado(); @endphp

                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-semibold">{{ \App\Support\Hora::formatear($cita->inicio, $negocio->zona_horaria) }}</span>
                        <x-filament::badge :color="$cita->estado->getColor()">
                            {{ $cita->estado->getLabel() }}
                        </x-filament::badge>
                    </div>

                    <p class="mt-2 text-base font-medium">{{ $cita->cliente->nombre }}</p>
                    <p class="text-sm text-gray-500">{{ $cita->servicio->nombre }}</p>

                    @if ($cita->notas)
                        <p class="mt-2 text-sm italic text-gray-500">"{{ $cita->notas }}"</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($estado->puedeTransicionarA(\App\Enums\EstadoCitaValor::EnAtencion))
                            <x-filament::button size="sm" color="primary" wire:click="marcarEnAtencion({{ $cita->id }})">
                                En atención
                            </x-filament::button>
                        @endif

                        @if ($estado->puedeTransicionarA(\App\Enums\EstadoCitaValor::Completada))
                            <x-filament::button size="sm" color="success" wire:click="marcarCompletada({{ $cita->id }})">
                                Completada
                            </x-filament::button>
                        @endif

                        @if ($estado->puedeTransicionarA(\App\Enums\EstadoCitaValor::NoAsistio))
                            <x-filament::button size="sm" color="gray" wire:click="marcarNoAsistio({{ $cita->id }})">
                                No asistió
                            </x-filament::button>
                        @endif
                    </div>
                </x-filament::card>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
