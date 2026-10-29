<x-filament-panels::page>
    @php
        $indicesEditables = $this->indicesEditables();
        $bloques = $this->bloques;
    @endphp

    @if (empty($indicesEditables))
        <x-filament::empty-state
            icon="heroicon-o-document-text"
            heading="Nada que editar todavía"
            description="Los bloques de tu página no tienen texto propio, o aún no tienen contenido. Pídele al super admin que agregue un bloque de portada o de texto."
        />
    @else
        <div class="space-y-4">
            @foreach ($indicesEditables as $indice)
                <x-filament::card>
                    <p class="mb-3 text-sm font-semibold capitalize">{{ $bloques[$indice]['type'] }}</p>

                    @if (array_key_exists('titulo', $bloques[$indice]['data'] ?? []))
                        <label class="mb-3 block">
                            <span class="fi-fo-field-wrp-label text-sm font-medium">Título</span>
                            <input type="text" wire:model="bloques.{{ $indice }}.data.titulo"
                                class="fi-input mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800" />
                        </label>
                    @endif

                    @if (array_key_exists('subtitulo', $bloques[$indice]['data'] ?? []))
                        <label class="mb-3 block">
                            <span class="fi-fo-field-wrp-label text-sm font-medium">Subtítulo</span>
                            <input type="text" wire:model="bloques.{{ $indice }}.data.subtitulo"
                                class="fi-input mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800" />
                        </label>
                    @endif

                    @if (array_key_exists('parrafo', $bloques[$indice]['data'] ?? []))
                        <label class="block">
                            <span class="fi-fo-field-wrp-label text-sm font-medium">Párrafo</span>
                            <textarea wire:model="bloques.{{ $indice }}.data.parrafo" rows="4"
                                class="fi-input mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800"></textarea>
                        </label>
                    @endif
                </x-filament::card>
            @endforeach

            <x-filament::button color="success" wire:click="guardarYPublicar">
                Guardar y publicar
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
