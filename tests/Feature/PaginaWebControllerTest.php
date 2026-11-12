<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Enums\TipoBloque;
use App\Models\Negocio;
use App\Models\Pagina;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('un dueno sin permiso de editar la pagina no puede entrar', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => false]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    Pagina::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/pagina-web")->assertForbidden();
    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/pagina")->assertForbidden();
});

test('un profesional nunca puede entrar, aunque el negocio lo permita', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    Pagina::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/pagina-web")->assertForbidden();
});

test('el dueno con permiso puede editar el texto de un bloque y publicarlo directo', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [
            ['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Viejo título', 'subtitulo' => '']],
            ['type' => TipoBloque::Equipo->value, 'data' => []],
        ],
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pagina/guardar-y-publicar", [
        'bloques' => [
            ['indice' => 0, 'data' => ['titulo' => 'Nuevo título', 'subtitulo' => '']],
        ],
    ]);

    $respuesta->assertOk();
    $pagina->refresh();
    expect($pagina->bloques[0]['data']['titulo'])->toBe('Nuevo título');
    expect($pagina->bloques_publicados[0]['data']['titulo'])->toBe('Nuevo título');
    expect($pagina->estaPublicada())->toBeTrue();
});

test('la api no incluye bloques sin texto propio', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [
            ['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Hola']],
            ['type' => TipoBloque::Equipo->value, 'data' => []],
            ['type' => TipoBloque::Whatsapp->value, 'data' => []],
        ],
    ]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/pagina");

    $respuesta->assertOk();
    expect($respuesta->json('bloques'))->toHaveCount(1);
    expect($respuesta->json('bloques.0.indice'))->toBe(0);
});

test('la api incluye los bloques nuevos con texto pero no encabezado, testimonios ni pie de pagina', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [
            ['type' => TipoBloque::BarraPromocion->value, 'data' => ['texto' => 'Oferta']],
            ['type' => TipoBloque::Encabezado->value, 'data' => ['enlaces' => []]],
            ['type' => TipoBloque::Testimonios->value, 'data' => ['testimonios' => []]],
            ['type' => TipoBloque::PieDePagina->value, 'data' => ['facebookUrl' => '']],
        ],
    ]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/pagina");

    $respuesta->assertOk();
    expect($respuesta->json('bloques'))->toHaveCount(1);
    expect($respuesta->json('bloques.0.indice'))->toBe(0);
});

test('el dueno puede subir una foto para un bloque', function (): void {
    Storage::fake('public');
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pagina/imagenes", [
        'imagen' => UploadedFile::fake()->create('portada.jpg', 100, 'image/jpeg'),
    ]);

    $respuesta->assertOk();
    expect($respuesta->json('ruta'))->not->toBeNull();
    Storage::disk('public')->assertExists($respuesta->json('ruta'));
});

test('un archivo que no es imagen no se guarda', function (): void {
    Storage::fake('public');
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pagina/imagenes", [
        'imagen' => UploadedFile::fake()->create('documento.pdf', 100),
    ]);

    $respuesta->assertStatus(422);
});

test('el dueno puede agregar fotos nuevas a la galeria sin perder las que ya tenia', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Galeria->value, 'data' => ['fotos' => ['https://ejemplo.test/vieja.jpg']]]],
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pagina/guardar-y-publicar", [
        'bloques' => [
            ['indice' => 0, 'data' => ['fotos' => ['https://ejemplo.test/vieja.jpg', 'https://ejemplo.test/nueva.jpg']]],
        ],
    ]);

    $respuesta->assertOk();
    $fotos = $pagina->refresh()->bloques[0]['data']['fotos'];
    expect($fotos)->toHaveCount(2);
    expect($fotos)->toContain('https://ejemplo.test/vieja.jpg', 'https://ejemplo.test/nueva.jpg');
});

test('el dueno puede editar el texto y la foto de un bloque de texto con imagen', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::TextoConImagen->value,
            'data' => ['titulo' => 'Viejo', 'parrafo' => 'viejo parrafo', 'fraseResaltada' => '', 'imagen' => null, 'colorFondo' => '#FFFFFF', 'colorTexto' => '#3E4043'],
        ]],
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pagina/guardar-y-publicar", [
        'bloques' => [
            ['indice' => 0, 'data' => ['titulo' => 'Nuevo', 'fraseResaltada' => 'palabra clave', 'imagen' => 'https://ejemplo.test/foto.jpg']],
        ],
    ]);

    $respuesta->assertOk();
    $datos = $pagina->refresh()->bloques[0]['data'];
    expect($datos['titulo'])->toBe('Nuevo');
    expect($datos['fraseResaltada'])->toBe('palabra clave');
    expect($datos['imagen'])->toBe('https://ejemplo.test/foto.jpg');
});

test('el dueno nunca puede cambiar el color de un bloque, aunque lo mande en el payload', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Texto->value,
            'data' => ['titulo' => 'Hola', 'parrafo' => 'Texto', 'colorFondo' => '#FFFFFF', 'colorTexto' => '#3E4043'],
        ]],
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pagina/guardar-y-publicar", [
        'bloques' => [
            ['indice' => 0, 'data' => ['titulo' => 'Hola', 'parrafo' => 'Texto', 'colorFondo' => '#000000', 'colorTexto' => '#000000']],
        ],
    ]);

    $respuesta->assertOk();
    $bloque = $pagina->refresh()->bloques[0];
    expect($bloque['type'])->toBe(TipoBloque::Texto->value);
    expect($bloque['data']['colorFondo'])->toBe('#FFFFFF');
    expect($bloque['data']['colorTexto'])->toBe('#3E4043');
});

test('un dueno no puede editar la pagina de otro negocio aunque adivine el slug', function (): void {
    $negocioA = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $negocioB = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $paginaB = Pagina::factory()->create([
        'negocio_id' => $negocioB->id,
        'bloques' => [['type' => TipoBloque::Texto->value, 'data' => ['titulo' => 'De B', 'parrafo' => '']]],
    ]);

    $this->actingAs($duenoA)->postJson("/app/{$negocioB->slug}/api/pagina/guardar-y-publicar", [
        'bloques' => [['indice' => 0, 'data' => ['titulo' => 'Hackeado']]],
    ])->assertNotFound();

    expect($paginaB->refresh()->bloques[0]['data']['titulo'])->toBe('De B');
});
