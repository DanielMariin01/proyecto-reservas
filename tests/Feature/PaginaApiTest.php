<?php

declare(strict_types=1);

use App\Actions\PublicarPagina;
use App\Enums\EstadoSuscripcion;
use App\Enums\RolNegocio;
use App\Enums\TipoBloque;
use App\Enums\TipoNegocio;
use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\PaletaMarca;
use App\Models\Usuario;

function negocioConPaginaBorradorYPublicada(): array
{
    $negocio = Negocio::factory()->create(['nombre' => 'Barbería Demo']);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Texto->value, 'data' => ['titulo' => 'Borrador nuevo']]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);
    $pagina->update(['bloques' => [['type' => TipoBloque::Texto->value, 'data' => ['titulo' => 'Borrador sin publicar']]]]);

    return ['negocio' => $negocio, 'pagina' => $pagina->refresh()];
}

test('sin sesion, la api siempre devuelve los bloques publicados, nunca el borrador', function (): void {
    ['negocio' => $negocio] = negocioConPaginaBorradorYPublicada();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina?vista_previa=1");

    $respuesta->assertOk();
    $respuesta->assertJsonPath('data.bloques.0.datos.titulo', 'Borrador nuevo');
    $respuesta->assertJsonPath('data.esVistaPrevia', false);
});

test('el super admin si puede ver el borrador con vista_previa', function (): void {
    ['negocio' => $negocio] = negocioConPaginaBorradorYPublicada();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->getJson("/api/publico/{$negocio->slug}/pagina?vista_previa=1");

    $respuesta->assertJsonPath('data.bloques.0.datos.titulo', 'Borrador sin publicar');
    $respuesta->assertJsonPath('data.esVistaPrevia', true);
});

test('el dueno ve el borrador con vista_previa solo si permite_editar_pagina esta activo', function (): void {
    ['negocio' => $negocio] = negocioConPaginaBorradorYPublicada();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $sinPermiso = $this->actingAs($dueno)->getJson("/api/publico/{$negocio->slug}/pagina?vista_previa=1");
    $sinPermiso->assertJsonPath('data.esVistaPrevia', false);
    $sinPermiso->assertJsonPath('data.bloques.0.datos.titulo', 'Borrador nuevo');

    $negocio->update(['permite_editar_pagina' => true]);

    $conPermiso = $this->actingAs($dueno)->getJson("/api/publico/{$negocio->slug}/pagina?vista_previa=1");
    $conPermiso->assertJsonPath('data.esVistaPrevia', true);
    $conPermiso->assertJsonPath('data.bloques.0.datos.titulo', 'Borrador sin publicar');
});

test('un negocio suspendido no responde en la api de pagina', function (): void {
    $negocio = Negocio::factory()->create(['estado_suscripcion' => EstadoSuscripcion::Suspendido]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertStatus(404);
});

test('un negocio sin pagina todavia responde con bloques vacios, no con error', function (): void {
    $negocio = Negocio::factory()->create();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertOk();
    $respuesta->assertJsonPath('data.bloques', []);
});

test('la api expone los colores de la paleta de marca del negocio', function (): void {
    $paleta = PaletaMarca::factory()->create(['color_primario' => '#1B263B', 'color_secundario' => '#415A77']);
    $negocio = Negocio::factory()->create(['paleta_marca_id' => $paleta->id]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonPath('data.colorPrimario', '#1B263B');
    $respuesta->assertJsonPath('data.colorSecundario', '#415A77');
});

test('sin paleta de marca asignada, la api devuelve los colores en null', function (): void {
    $negocio = Negocio::factory()->create(['paleta_marca_id' => null]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonPath('data.colorPrimario', null);
    $respuesta->assertJsonPath('data.colorSecundario', null);
});

test('la api traduce la foto de cada testimonio a una url completa', function (): void {
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Testimonios->value,
            'data' => ['fuente' => 'manual', 'testimonios' => [
                ['cita' => 'Muy buen servicio', 'nombre' => 'Luisa', 'foto' => 'paginas/luisa.jpg', 'estrellas' => 5],
            ]],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonPath('data.bloques.0.datos.testimonios.0.nombre', 'Luisa');
    $url = $respuesta->json('data.bloques.0.datos.testimonios.0.foto');
    expect($url)->toContain('/storage/paginas/luisa.jpg');
});

test('la api traduce la imagen de cada tarjeta a una url completa', function (): void {
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Tarjetas->value,
            'data' => ['columnas' => 2, 'tarjetas' => [
                ['imagen' => 'paginas/paso1.jpg', 'titulo' => 'Paso 1', 'descripcion' => 'Agenda'],
            ]],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $url = $respuesta->json('data.bloques.0.datos.tarjetas.0.imagen');
    expect($url)->toContain('/storage/paginas/paso1.jpg');
});

test('la api expone la etiqueta legible del tipo de negocio', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Barberia]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonPath('data.tipoEtiqueta', 'Barbería');
});
