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

                    @if (array_key_exists('imagen', $bloques[$indice]['data'] ?? []))
                        <label class="mt-3 block">
                            <span class="fi-fo-field-wrp-label text-sm font-medium">Imagen</span>
                            @if (is_string($bloques[$indice]['data']['imagen'] ?? null) && $bloques[$indice]['data']['imagen'] !== '')
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($bloques[$indice]['data']['imagen']) }}"
                                    alt="" class="mb-2 h-32 w-full rounded-lg object-cover" />
                            @endif
                            <input type="file" accept="image/*" wire:model="bloques.{{ $indice }}.data.imagen"
                                class="fi-input mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800" />
                            <span wire:loading wire:target="bloques.{{ $indice }}.data.imagen" class="text-xs text-texto-suave">Subiendo...</span>
                        </label>
                    @endif

                    @if (array_key_exists('fotos', $bloques[$indice]['data'] ?? []))
                        <div class="mt-3">
                            <span class="fi-fo-field-wrp-label text-sm font-medium">Fotos de la galería</span>

                            @if (!empty($bloques[$indice]['data']['fotos']))
                                <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                    @foreach ($bloques[$indice]['data']['fotos'] as $posicion => $foto)
                                        <div class="relative">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($foto) }}"
                                                alt="" class="h-20 w-full rounded-lg object-cover" />
                                            <button type="button" wire:click="quitarFotoExistente({{ $indice }}, {{ $posicion }})"
                                                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-tinta text-xs text-blanco">
                                                ×
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <input type="file" accept="image/*" multiple wire:model="fotosNuevasPorIndice.{{ $indice }}"
                                class="fi-input mt-2 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800" />
                            <span wire:loading wire:target="fotosNuevasPorIndice.{{ $indice }}" class="text-xs text-texto-suave">Subiendo...</span>
                        </div>
                    @endif
                </x-filament::card>
            @endforeach

            <x-filament::button color="success" wire:click="guardarYPublicar">
                Guardar y publicar
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
