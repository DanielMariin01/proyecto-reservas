<x-filament-panels::page>
    <x-filament::card>
        <h3 class="text-base font-semibold">Bloqueo rápido</h3>
        <p class="text-sm text-gray-500">"No atiendo hoy de 3 a 5": elige la fecha y el rango de horas.</p>

        <form wire:submit="crearBloqueo" class="mt-4 space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit">
                Bloquear estas horas
            </x-filament::button>
        </form>
    </x-filament::card>

    <x-filament::card>
        <h3 class="text-base font-semibold">Mi horario semanal</h3>

        @php $horarios = $this->horariosTrabajo(); @endphp

        @if ($horarios->isEmpty())
            <x-filament::empty-state
                icon="heroicon-o-calendar"
                heading="Aún no tienes un horario"
                description="Pídele al dueño del negocio que configure tu horario semanal."
            />
        @else
            <ul class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($horarios as $horario)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span class="font-medium">{{ $horario->dia_semana->getLabel() }}</span>
                        <span class="text-gray-500">{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::card>

    <x-filament::card>
        <h3 class="text-base font-semibold">Mis próximos bloqueos</h3>

        @php $bloqueos = $this->bloqueosProximos(); @endphp

        @if ($bloqueos->isEmpty())
            <x-filament::empty-state
                icon="heroicon-o-clock"
                heading="No tienes bloqueos próximos"
                description="Los bloqueos que crees arriba aparecerán aquí."
            />
        @else
            <ul class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($bloqueos as $bloqueo)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>
                            <span class="font-medium">{{ $bloqueo->tipo->getLabel() }}</span>
                            <span class="text-gray-500">{{ $bloqueo->motivo ?? 'Sin motivo' }}</span>
                        </span>
                        <x-filament::icon-button
                            icon="heroicon-o-trash"
                            color="danger"
                            label="Eliminar"
                            wire:click="eliminarBloqueo({{ $bloqueo->id }})"
                            wire:confirm="¿Eliminar este bloqueo?"
                        />
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::card>
</x-filament-panels::page>
