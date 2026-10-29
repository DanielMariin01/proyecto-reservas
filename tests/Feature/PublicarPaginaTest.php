<?php

declare(strict_types=1);

use App\Actions\PublicarPagina;
use App\Enums\TipoBloque;
use App\Events\PaginaPublicada;
use App\Models\Negocio;
use App\Models\Pagina;
use Illuminate\Support\Facades\Event;

test('publicar copia el borrador a bloques_publicados y marca la fecha', function (): void {
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Hola']]],
        'bloques_publicados' => null,
        'publicada_en' => null,
    ]);

    $pagina = app(PublicarPagina::class)->ejecutar($pagina);

    expect($pagina->bloques_publicados)->toBe($pagina->bloques);
    expect($pagina->publicada_en)->not->toBeNull();
    expect($pagina->estaPublicada())->toBeTrue();
});

test('publicar de nuevo despues de editar el borrador actualiza lo publicado', function (): void {
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->publicada()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => TipoBloque::Texto->value, 'data' => ['titulo' => 'Viejo']]],
    ]);

    $pagina->update(['bloques' => [['type' => TipoBloque::Texto->value, 'data' => ['titulo' => 'Nuevo']]]]);
    $pagina = app(PublicarPagina::class)->ejecutar($pagina->refresh());

    expect($pagina->bloques_publicados[0]['data']['titulo'])->toBe('Nuevo');
});

test('dispara el evento PaginaPublicada', function (): void {
    Event::fake();
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->create(['negocio_id' => $negocio->id]);

    app(PublicarPagina::class)->ejecutar($pagina);

    Event::assertDispatched(PaginaPublicada::class);
});
