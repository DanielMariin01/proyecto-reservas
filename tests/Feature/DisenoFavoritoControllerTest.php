<?php

declare(strict_types=1);

use App\Models\CuentaCliente;
use App\Models\DisenoFavorito;
use App\Models\Negocio;
use App\Models\Pagina;

/**
 * @return array{negocio: Negocio}
 */
function negocioConDisenoPublicado(string $disenoId = 'diseno-francesa'): array
{
    $negocio = Negocio::factory()->create();

    Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques_publicados' => [
            [
                'type' => 'mis_disenos',
                'data' => [
                    'items' => [
                        ['id' => $disenoId, 'imagen' => 'paginas/francesa.jpg', 'titulo' => 'Francesa', 'descripcion' => 'Clásica'],
                    ],
                ],
            ],
        ],
    ]);

    return ['negocio' => $negocio];
}

test('marcar un diseno como favorito lo crea y marcarlo de nuevo lo quita', function (): void {
    ['negocio' => $negocio] = negocioConDisenoPublicado();
    $cuenta = CuentaCliente::factory()->create();

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson('/mi-cuenta/api/disenos-favoritos/alternar', [
        'negocio_slug' => $negocio->slug,
        'diseno_id' => 'diseno-francesa',
    ]);
    $respuesta->assertOk();
    expect($respuesta->json('favorito'))->toBeTrue();
    $this->assertDatabaseHas('disenos_favoritos', ['cuenta_cliente_id' => $cuenta->id, 'negocio_id' => $negocio->id, 'diseno_id' => 'diseno-francesa']);

    $respuestaQuitar = $this->actingAs($cuenta, 'cliente')->postJson('/mi-cuenta/api/disenos-favoritos/alternar', [
        'negocio_slug' => $negocio->slug,
        'diseno_id' => 'diseno-francesa',
    ]);
    $respuestaQuitar->assertOk();
    expect($respuestaQuitar->json('favorito'))->toBeFalse();
    $this->assertDatabaseMissing('disenos_favoritos', ['cuenta_cliente_id' => $cuenta->id, 'negocio_id' => $negocio->id]);
});

test('no se puede marcar favorito un diseno que no existe en la pagina publicada', function (): void {
    ['negocio' => $negocio] = negocioConDisenoPublicado();
    $cuenta = CuentaCliente::factory()->create();

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson('/mi-cuenta/api/disenos-favoritos/alternar', [
        'negocio_slug' => $negocio->slug,
        'diseno_id' => 'diseno-inventado',
    ]);

    $respuesta->assertNotFound();
});

test('la lista de favoritos trae los datos del diseno y omite los que ya no existen', function (): void {
    ['negocio' => $negocio] = negocioConDisenoPublicado();
    $cuenta = CuentaCliente::factory()->create();

    DisenoFavorito::factory()->create(['cuenta_cliente_id' => $cuenta->id, 'negocio_id' => $negocio->id, 'diseno_id' => 'diseno-francesa']);
    DisenoFavorito::factory()->create(['cuenta_cliente_id' => $cuenta->id, 'negocio_id' => $negocio->id, 'diseno_id' => 'diseno-borrado']);

    $respuesta = $this->actingAs($cuenta, 'cliente')->getJson('/mi-cuenta/api/disenos-favoritos');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');
    expect($datos)->toHaveCount(1);
    expect($datos[0]['titulo'])->toBe('Francesa');
    expect($datos[0]['negocioSlug'])->toBe($negocio->slug);
});

test('los ids favoritos de un negocio solo incluyen los de la cuenta actual', function (): void {
    ['negocio' => $negocio] = negocioConDisenoPublicado();
    $cuentaPropia = CuentaCliente::factory()->create();
    $cuentaAjena = CuentaCliente::factory()->create();

    DisenoFavorito::factory()->create(['cuenta_cliente_id' => $cuentaPropia->id, 'negocio_id' => $negocio->id, 'diseno_id' => 'diseno-francesa']);
    DisenoFavorito::factory()->create(['cuenta_cliente_id' => $cuentaAjena->id, 'negocio_id' => $negocio->id, 'diseno_id' => 'otro-diseno']);

    $respuesta = $this->actingAs($cuentaPropia, 'cliente')->getJson("/mi-cuenta/api/disenos-favoritos/negocio/{$negocio->slug}");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toBe(['diseno-francesa']);
});
