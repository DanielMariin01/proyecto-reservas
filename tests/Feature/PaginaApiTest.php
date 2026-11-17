<?php

declare(strict_types=1);

use App\Actions\PublicarPagina;
use App\Enums\EstadoCitaValor;
use App\Enums\EstadoSuscripcion;
use App\Enums\RolNegocio;
use App\Enums\TipoBloque;
use App\Enums\TipoNegocio;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\PaletaMarca;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

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

test('la api traduce la imagen de cada diseno del bloque mis_disenos a una url completa', function (): void {
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::MisDisenos->value,
            'data' => ['items' => [
                ['imagen' => 'paginas/disenio1.jpg', 'titulo' => 'Francesas', 'descripcion' => 'Uñas francesas en gel'],
            ]],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $url = $respuesta->json('data.bloques.0.datos.items.0.imagen');
    expect($url)->toContain('/storage/paginas/disenio1.jpg');
});

test('con fuente sistema, el bloque testimonios muestra las reseñas reales publicadas', function (): void {
    $negocio = Negocio::factory()->create();
    Resena::factory()->create(['negocio_id' => $negocio->id, 'calificacion' => 5, 'comentario' => 'Reseña publicada visible']);
    Resena::factory()->oculta()->create(['negocio_id' => $negocio->id, 'calificacion' => 1, 'comentario' => 'Reseña oculta, no debería salir']);

    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Testimonios->value,
            'data' => ['fuente' => 'sistema'],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonCount(1, 'data.bloques.0.datos.testimonios');
    $respuesta->assertJsonPath('data.bloques.0.datos.testimonios.0.cita', 'Reseña publicada visible');
    $respuesta->assertJsonPath('data.bloques.0.datos.testimonios.0.estrellas', 5);
});

test('con fuente sistema, el bloque testimonios incluye el resumen de reseñas del negocio', function (): void {
    $negocio = Negocio::factory()->create(['promedio_calificacion' => 4.67, 'total_resenas' => 3]);

    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Testimonios->value,
            'data' => ['fuente' => 'sistema'],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonPath('data.bloques.0.datos.resumen.promedio', 4.67);
    $respuesta->assertJsonPath('data.bloques.0.datos.resumen.total', 3);
});

test('con fuente manual, el bloque testimonios no incluye el resumen de reseñas', function (): void {
    $negocio = Negocio::factory()->create();

    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Testimonios->value,
            'data' => ['fuente' => 'manual', 'testimonios' => []],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonMissingPath('data.bloques.0.datos.resumen');
});

test('el bloque servicios_destacados devuelve el ranking real ordenado por puesto', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $masPedido = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Corte clásico']);
    $menosPedido = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Barba']);

    Cita::factory()->count(3)->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $masPedido->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $menosPedido->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);

    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::ServiciosDestacados->value,
            'data' => ['cantidad' => 2],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonCount(2, 'data.bloques.0.datos.servicios');
    $respuesta->assertJsonPath('data.bloques.0.datos.servicios.0.nombre', 'Corte clásico');
    $respuesta->assertJsonPath('data.bloques.0.datos.servicios.0.puesto', 1);
    $respuesta->assertJsonPath('data.bloques.0.datos.servicios.0.totalReservas', 3);
    $respuesta->assertJsonPath('data.bloques.0.datos.servicios.0.clientesDistintos', 3);
    $respuesta->assertJsonPath('data.bloques.0.datos.servicios.1.nombre', 'Barba');
    $respuesta->assertJsonPath('data.bloques.0.datos.servicios.1.puesto', 2);
});

test('el bloque servicios_destacados no incluye un servicio que ya no esta visible', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $servicioOculto = Servicio::factory()->create(['negocio_id' => $negocio->id, 'visible' => false]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicioOculto->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);

    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::ServiciosDestacados->value,
            'data' => ['cantidad' => 4],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonCount(0, 'data.bloques.0.datos.servicios');
});

test('sin citas completadas, el bloque servicios_destacados devuelve una lista vacia sin error', function (): void {
    $negocio = Negocio::factory()->create();
    Servicio::factory()->create(['negocio_id' => $negocio->id]);

    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::ServiciosDestacados->value,
            'data' => ['cantidad' => 4],
        ]],
    ]);
    app(PublicarPagina::class)->ejecutar($pagina);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertOk();
    $respuesta->assertJsonCount(0, 'data.bloques.0.datos.servicios');
});

test('la api expone la etiqueta legible del tipo de negocio', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Barberia]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/pagina");

    $respuesta->assertJsonPath('data.tipoEtiqueta', 'Barbería');
});
