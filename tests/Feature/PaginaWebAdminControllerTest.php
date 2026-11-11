<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('un usuario normal no puede ver ni modificar los bloques de la pagina', function (): void {
    $usuario = Usuario::factory()->create();
    $negocio = Negocio::factory()->create();

    $this->actingAs($usuario)->getJson("/admin/api/negocios/{$negocio->slug}/pagina")->assertForbidden();
    $this->actingAs($usuario)->putJson("/admin/api/negocios/{$negocio->slug}/pagina", ['bloques' => []])->assertForbidden();
    $this->actingAs($usuario)->postJson("/admin/api/negocios/{$negocio->slug}/pagina/publicar")->assertForbidden();
});

test('un negocio sin pagina todavia devuelve bloques vacios sin publicar', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->getJson("/admin/api/negocios/{$negocio->slug}/pagina");

    $respuesta->assertOk();
    expect($respuesta->json('data.bloques'))->toBe([]);
    expect($respuesta->json('data.estaPublicada'))->toBeFalse();
});

test('guardar bloques crea la pagina si todavia no existe', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/negocios/{$negocio->slug}/pagina", [
        'bloques' => [
            ['type' => 'portada', 'data' => ['titulo' => 'Bienvenido']],
            ['type' => 'servicios', 'data' => ['colorFondo' => '#FFFFFF', 'colorTexto' => '#3E4043']],
        ],
    ]);

    $respuesta->assertOk();

    $pagina = Pagina::query()->where('negocio_id', $negocio->id)->firstOrFail();
    expect($pagina->bloques)->toHaveCount(2);
    expect($pagina->bloques[0]['type'])->toBe('portada');
});

test('guardar bloques sin el campo requerido de un tipo falla la validacion', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/negocios/{$negocio->slug}/pagina", [
        'bloques' => [
            ['type' => 'barra_promocion', 'data' => ['textoBoton' => 'Ver más']],
        ],
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['bloques.0']);
});

test('guardar bloques con un tipo desconocido falla la validacion', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/negocios/{$negocio->slug}/pagina", [
        'bloques' => [
            ['type' => 'tipo_inventado', 'data' => []],
        ],
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['bloques.0']);
});

test('publicar copia los bloques guardados a los publicados', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();
    $pagina = Pagina::factory()->create([
        'negocio_id' => $negocio->id,
        'bloques' => [['type' => 'portada', 'data' => ['titulo' => 'Hola']]],
        'bloques_publicados' => null,
        'publicada_en' => null,
    ]);

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/negocios/{$negocio->slug}/pagina/publicar");

    $respuesta->assertOk();
    $pagina->refresh();

    expect($pagina->bloques_publicados)->toBe($pagina->bloques);
    expect($pagina->publicada_en)->not->toBeNull();
});

test('publicar sin haber guardado la pagina antes avisa en vez de fallar', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/negocios/{$negocio->slug}/pagina/publicar");

    $respuesta->assertStatus(422);
    expect(Pagina::query()->where('negocio_id', $negocio->id)->exists())->toBeFalse();
});

test('subir una imagen para un bloque la guarda en el disco publico', function (): void {
    Storage::fake('public');
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/negocios/{$negocio->slug}/pagina/imagenes", [
        'imagen' => UploadedFile::fake()->create('portada.jpg', 100, 'image/jpeg'),
    ]);

    $respuesta->assertOk();
    Storage::disk('public')->assertExists($respuesta->json('ruta'));
});
