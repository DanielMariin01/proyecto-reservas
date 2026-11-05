<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * @return array{negocio: Negocio, profesional: Profesional, cita: Cita, token: string}
 */
function negocioConCitaCompletadaConToken(): array
{
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $token = Str::random(40);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Completada,
        'token_cancelacion' => $token,
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'cita' => $cita, 'token' => $token];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('ver una cita completada por token ofrece dejar una reseña', function (): void {
    ['negocio' => $negocio, 'token' => $token] = negocioConCitaCompletadaConToken();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/reservas/{$token}");

    $respuesta->assertOk();
    $respuesta->assertJsonPath('data.puedeDejarResena', true);
    $respuesta->assertJsonPath('data.resena', null);
});

test('dejar una reseña por token la guarda asociada al negocio y al profesional', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'cita' => $cita, 'token' => $token] = negocioConCitaCompletadaConToken();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'resena',
        'calificacion' => 5,
        'comentario' => 'Quedé muy contenta con el servicio.',
    ]);

    $respuesta->assertOk();
    $respuesta->assertJsonPath('data.puedeDejarResena', false);
    $respuesta->assertJsonPath('data.resena.calificacion', 5);

    $resena = Resena::query()->where('cita_id', $cita->id)->firstOrFail();
    expect($resena->negocio_id)->toBe($negocio->id);
    expect($resena->profesional_id)->toBe($profesional->id);
});

test('no se puede dejar una reseña de una cita que no esta completada', function (): void {
    ['negocio' => $negocio, 'cita' => $cita, 'token' => $token] = negocioConCitaCompletadaConToken();
    $cita->update(['estado' => EstadoCitaValor::Confirmada]);

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'resena',
        'calificacion' => 5,
        'comentario' => 'No debería poder enviar esto.',
    ]);

    $respuesta->assertStatus(422);
    expect(Resena::query()->where('cita_id', $cita->id)->exists())->toBeFalse();
});

test('no se puede dejar una segunda reseña con el mismo token', function (): void {
    ['negocio' => $negocio, 'token' => $token] = negocioConCitaCompletadaConToken();

    $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'resena',
        'calificacion' => 4,
        'comentario' => 'Primera reseña, todo bien.',
    ])->assertOk();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'resena',
        'calificacion' => 2,
        'comentario' => 'Segunda reseña, no debería pasar.',
    ]);

    $respuesta->assertStatus(422);
});

test('el comentario muy corto no pasa la validacion', function (): void {
    ['negocio' => $negocio, 'token' => $token] = negocioConCitaCompletadaConToken();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'resena',
        'calificacion' => 5,
        'comentario' => 'Bien',
    ]);

    $respuesta->assertStatus(422);
    $respuesta->assertJsonValidationErrors(['comentario']);
});

test('el token de un negocio no deja reseñar la cita de otro negocio', function (): void {
    ['token' => $token] = negocioConCitaCompletadaConToken();
    $otroNegocio = Negocio::factory()->create();

    $respuesta = $this->postJson("/api/publico/{$otroNegocio->slug}/reservas/{$token}", [
        'accion' => 'resena',
        'calificacion' => 5,
        'comentario' => 'Esto no debería funcionar nunca.',
    ]);

    $respuesta->assertNotFound();
});
