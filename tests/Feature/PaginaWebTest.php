<?php

declare(strict_types=1);

use App\Enums\CombinacionColorBloque;
use App\Enums\RolNegocio;
use App\Enums\TipoBloque;
use App\Filament\App\Pages\PaginaWeb;
use App\Models\Negocio;
use App\Models\Pagina;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('un dueno sin permiso de editar la pagina no puede entrar', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => false]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    Pagina::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/pagina-web");

    $respuesta->assertForbidden();
});

test('un profesional nunca puede entrar, aunque el negocio lo permita', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    Pagina::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/pagina-web");

    $respuesta->assertForbidden();
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

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(PaginaWeb::class)
        ->assertSet('bloques.0.data.titulo', 'Viejo título')
        ->set('bloques.0.data.titulo', 'Nuevo título')
        ->call('guardarYPublicar');

    $pagina->refresh();
    expect($pagina->bloques[0]['data']['titulo'])->toBe('Nuevo título');
    expect($pagina->bloques_publicados[0]['data']['titulo'])->toBe('Nuevo título');
    expect($pagina->estaPublicada())->toBeTrue();
});

test('indicesEditables no incluye bloques sin texto propio', function (): void {
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

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    $indices = Livewire::actingAs($dueno)->test(PaginaWeb::class)->instance()->indicesEditables();

    expect($indices)->toBe([0]);
});

test('el dueno puede subir una foto nueva de portada y queda guardada en el disco publico', function (): void {
    Storage::fake('public');
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Hola', 'imagen' => null]]],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(PaginaWeb::class)
        ->set('bloques.0.data.imagen', UploadedFile::fake()->create('portada.jpg', 100, 'image/jpeg'))
        ->call('guardarYPublicar');

    $pagina->refresh();
    expect($pagina->bloques[0]['data']['imagen'])->not->toBeNull();
    Storage::disk('public')->assertExists($pagina->bloques[0]['data']['imagen']);
});

test('un archivo que no es imagen no se guarda y avisa en vez de publicar', function (): void {
    Storage::fake('public');
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Hola', 'imagen' => null]]],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(PaginaWeb::class)
        ->set('bloques.0.data.imagen', UploadedFile::fake()->create('documento.pdf', 100))
        ->call('guardarYPublicar');

    expect($pagina->refresh()->estaPublicada())->toBeFalse();
});

test('el dueno puede agregar fotos nuevas a la galeria sin perder las que ya tenia', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('paginas/vieja.jpg', 'contenido');
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Galeria->value, 'data' => ['fotos' => ['paginas/vieja.jpg']]]],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(PaginaWeb::class)
        ->set('fotosNuevasPorIndice.0', [UploadedFile::fake()->create('nueva.jpg', 100, 'image/jpeg')])
        ->call('guardarYPublicar');

    $fotos = $pagina->refresh()->bloques[0]['data']['fotos'];
    expect($fotos)->toHaveCount(2);
    expect($fotos)->toContain('paginas/vieja.jpg');
});

test('el dueno puede editar el texto y la foto de un bloque de texto con imagen', function (): void {
    Storage::fake('public');
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::TextoConImagen->value,
            'data' => ['titulo' => 'Viejo', 'parrafo' => 'viejo parrafo', 'fraseResaltada' => '', 'imagen' => null, 'combinacionColor' => CombinacionColorBloque::Clara->value],
        ]],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    $componente = Livewire::actingAs($dueno)->test(PaginaWeb::class);
    expect($componente->instance()->indicesEditables())->toBe([0]);

    $componente
        ->set('bloques.0.data.titulo', 'Nuevo')
        ->set('bloques.0.data.fraseResaltada', 'palabra clave')
        ->set('bloques.0.data.imagen', UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'))
        ->call('guardarYPublicar');

    $datos = $pagina->refresh()->bloques[0]['data'];
    expect($datos['titulo'])->toBe('Nuevo');
    expect($datos['fraseResaltada'])->toBe('palabra clave');
    expect($datos['imagen'])->not->toBeNull();
});

test('el dueno nunca puede cambiar la combinacion de color de un bloque, aunque lo mande en el payload', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [[
            'type' => TipoBloque::Texto->value,
            'data' => ['titulo' => 'Hola', 'parrafo' => 'Texto', 'combinacionColor' => CombinacionColorBloque::Clara->value],
        ]],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(PaginaWeb::class)
        ->set('bloques.0.data.combinacionColor', CombinacionColorBloque::Marca->value)
        ->set('bloques.0.type', TipoBloque::Portada->value)
        ->call('guardarYPublicar');

    $bloque = $pagina->refresh()->bloques[0];
    expect($bloque['type'])->toBe(TipoBloque::Texto->value);
    expect($bloque['data']['combinacionColor'])->toBe(CombinacionColorBloque::Clara->value);
});

test('quitar una foto existente de la galeria la elimina de los bloques', function (): void {
    $negocio = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Galeria->value, 'data' => ['fotos' => ['paginas/a.jpg', 'paginas/b.jpg']]]],
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(PaginaWeb::class)
        ->call('quitarFotoExistente', 0, 0)
        ->assertSet('bloques.0.data.fotos', ['paginas/b.jpg']);
});
