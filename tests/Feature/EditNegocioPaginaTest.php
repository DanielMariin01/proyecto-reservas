<?php

declare(strict_types=1);

use App\Enums\FuenteTestimonios;
use App\Enums\IconoBloque;
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
                ['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Bienvenido', 'etiqueta' => 'Agenda hoy', 'imagen' => null]],
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

test('el super admin arma un bloque de texto con imagen en modo motivos, con color libre', function (): void {
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
                        'etiqueta' => 'Por qué elegirnos',
                        'titulo' => 'La diferencia está en el detalle',
                        'motivos' => [
                            ['icono' => IconoBloque::Escudo->value, 'titulo' => 'Garantía', 'descripcion' => 'Satisfacción asegurada'],
                            ['icono' => IconoBloque::Reloj->value, 'titulo' => 'Puntualidad', 'descripcion' => 'Nunca te hacemos esperar'],
                        ],
                        'imagen' => null,
                        'colorFondo' => '#112233',
                        'colorTexto' => '#FFFFFF',
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();
    $datos = $pagina->bloques[0]['data'];

    expect($pagina->bloques[0]['type'])->toBe(TipoBloque::TextoConImagen->value);
    expect($datos['motivos'])->toHaveCount(2);
    expect($datos['motivos'][0]['icono'])->toBe(IconoBloque::Escudo->value);
    expect($datos['colorFondo'])->toBe('#112233');
});

test('una paleta o un bloque con poco contraste se guarda igual, solo con advertencia (no bloqueante)', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(EditNegocio::class, ['record' => $negocio->getRouteKey()])
        ->fillForm([
            'pagina.bloques' => [
                ['type' => TipoBloque::Texto->value, 'data' => ['titulo' => 'Hola', 'parrafo' => 'Texto', 'colorFondo' => '#EEEEEE', 'colorTexto' => '#F0F0F0']],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();
    expect($pagina->bloques[0]['data']['colorTexto'])->toBe('#F0F0F0');
});

test('el super admin arma los bloques nuevos de la plantilla de landing', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(EditNegocio::class, ['record' => $negocio->getRouteKey()])
        ->fillForm([
            'pagina.bloques' => [
                ['type' => TipoBloque::BarraPromocion->value, 'data' => ['texto' => '20% de descuento hoy', 'textoBoton' => 'Ver más', 'enlaceBoton' => 'https://ejemplo.com']],
                ['type' => TipoBloque::Encabezado->value, 'data' => ['enlaces' => [['texto' => 'Servicios', 'url' => '#servicios']]]],
                ['type' => TipoBloque::BannerOferta->value, 'data' => ['titulo' => 'Oferta de octubre', 'descripcion' => 'Solo este mes']],
                [
                    'type' => TipoBloque::Testimonios->value,
                    'data' => [
                        'fuente' => FuenteTestimonios::Manual->value,
                        'testimonios' => [
                            ['cita' => 'Excelente atención', 'nombre' => 'Ana', 'foto' => null, 'estrellas' => 5],
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();

    expect($pagina->bloques)->toHaveCount(4);
    expect($pagina->bloques[0]['type'])->toBe(TipoBloque::BarraPromocion->value);
    expect($pagina->bloques[1]['data']['enlaces'][0]['texto'])->toBe('Servicios');
    expect($pagina->bloques[3]['data']['testimonios'][0]['nombre'])->toBe('Ana');
    expect($pagina->bloques[3]['data']['testimonios'][0]['estrellas'])->toBe(5);
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
