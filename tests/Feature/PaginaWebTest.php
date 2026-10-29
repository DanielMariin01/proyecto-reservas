<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Enums\TipoBloque;
use App\Filament\App\Pages\PaginaWeb;
use App\Models\Negocio;
use App\Models\Pagina;
use Filament\Facades\Filament;
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
