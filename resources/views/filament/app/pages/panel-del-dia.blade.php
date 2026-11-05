<x-filament-panels::page>
    @php
        $negocio = \App\Models\Negocio::actual();
        $citasDeHoy = $this->citasDeHoy();
        $citasProximas = $this->citasProximas();
        $ingresos = $this->ingresosRegistrados();
        $huecos = $this->huecosLibres();
    @endphp

    <div class="grid gap-4 sm:grid-cols-3">
        <x-filament::card>
            <p class="text-sm text-gray-500">Citas de hoy</p>
            <p class="text-2xl font-bold">{{ $citasDeHoy->count() }}</p>
        </x-filament::card>

        <x-filament::card>
            <p class="text-sm text-gray-500">Próximas (aún no empiezan)</p>
            <p class="text-2xl font-bold">{{ $citasProximas->count() }}</p>
        </x-filament::card>

        <x-filament::card>
            <p class="text-sm text-gray-500">Ingresos registrados hoy</p>
            <p class="text-2xl font-bold">{{ \App\Support\Dinero::formatearPesos($ingresos) }}</p>
        </x-filament::card>
    </div>

    <x-filament::card>
        <h2 class="mb-3 text-base font-semibold">Citas de hoy</h2>

        @if ($citasDeHoy->isEmpty())
            <x-filament::empty-state
                icon="heroicon-o-calendar"
                heading="Sin citas hoy"
                description="Cuando reserven una hora para hoy, aparecerá aquí."
            />
        @else
            <ul class="space-y-2">
                @foreach ($citasDeHoy as $cita)
                    <li class="flex items-center justify-between rounded-tarjeta border border-gray-200 p-3 dark:border-gray-700">
                        <div>
                            <span class="text-sm font-semibold">{{ \App\Support\Hora::formatear($cita->inicio, $negocio->zona_horaria) }}</span>
                            <span class="ml-2 text-sm">{{ $cita->cliente->nombre }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $cita->servicio->nombre }} · {{ $cita->profesional->nombre }}</span>
                        </div>
                        <x-filament::badge :color="$cita->estado->getColor()">
                            {{ $cita->estado->getLabel() }}
                        </x-filament::badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::card>

    <x-filament::card>
        <h2 class="mb-3 text-base font-semibold">Huecos libres de hoy</h2>

        @if ($huecos->every(fn ($columna) => $columna['bloques']->isEmpty()))
            <x-filament::empty-state
                icon="heroicon-o-clock"
                heading="Sin huecos libres"
                description="La agenda de hoy está llena, o no hay profesionales activos."
            />
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($huecos as $columna)
                    @if ($columna['bloques']->isNotEmpty())
                        <div>
                            <p class="mb-1 text-sm font-semibold">{{ $columna['profesional']->nombre }}</p>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                @foreach ($columna['bloques'] as $bloque)
                                    <li>
                                        {{ \App\Support\Hora::formatear($bloque->inicio, $negocio->zona_horaria) }}
                                        –
                                        {{ \App\Support\Hora::formatear($bloque->fin, $negocio->zona_horaria) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </x-filament::card>
</x-filament-panels::page>
