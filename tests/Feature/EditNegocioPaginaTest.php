<?php

declare(strict_types=1);

use App\Enums\CombinacionColorBloque;
use App\Enums\TipoBloque;
use App\Filament\Resources\Negocios\Pages\EditNegocio;
use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\PaletaMarca;
use App\Models\Usuario;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('el super admin arma los bloques de la pagina con el Builder y crea la Pagina', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(EditNegocio::class, ['record' => $negocio->getRouteKey()])
        ->fillForm([
            'pagina.bloques' => [
                ['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Bienvenido', 'subtitulo' => 'Agenda hoy', 'imagen' => null]],
                ['type' => TipoBloque::Servicios->value, 'data' => []],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();

    expect($pagina)->not->toBeNull();
    expect($pagina->bloques)->toHaveCount(2);
    expect($pagina->bloques[0]['type'])->toBe(TipoBloque::Portada->value);
    expect($pagina->bloques[0]['data']['titulo'])->toBe('Bienvenido');
    expect($pagina->bloques_publicados)->toBeNull();
});

test('el super admin arma un bloque de texto con imagen con su combinacion de color', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(EditNegocio::class, ['record' => $negocio->getRouteKey()])
        ->fillForm([
            'pagina.bloques' => [
                [
                    'type' => TipoBloque::TextoConImagen->value,
                    'data' => [
                        'titulo' => 'Nuestra historia',
                        'parrafo' => 'Llevamos 10 años cuidando tu estilo.',
                        'fraseResaltada' => '10 años',
                        'imagen' => null,
                        'combinacionColor' => CombinacionColorBloque::Marca->value,
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();

    expect($pagina->bloques[0]['type'])->toBe(TipoBloque::TextoConImagen->value);
    expect($pagina->bloques[0]['data']['combinacionColor'])->toBe(CombinacionColorBloque::Marca->value);
    expect($pagina->bloques[0]['data']['fraseResaltada'])->toBe('10 años');
});

test('el super admin asigna una paleta de marca al negocio desde un select', function (): void {
    $negocio = Negocio::factory()->create();
    $paleta = PaletaMarca::factory()->create(['nombre' => 'Color Barbería de prueba']);
    $superAdmin = Usuario::factory()->superAdmin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(EditNegocio::class, ['record' => $negocio->getRouteKey()])
        ->fillForm(['paleta_marca_id' => $paleta->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($negocio->refresh()->paleta_marca_id)->toBe($paleta->id);
});

test('publicar la pagina sin haberla guardado antes avisa en vez de fallar', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(EditNegocio::class, ['record' => $negocio->getRouteKey()])
        ->callAction('publicarPagina');

    expect(Pagina::query()->where('negocio_id', $negocio->id)->exists())->toBeFalse();
});

test('un usuario que no es super admin no puede entrar a editar el negocio', function (): void {
    $usuario = Usuario::factory()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($usuario)->get("/admin/negocios/{$negocio->id}/edit");

    $respuesta->assertForbidden();
});
