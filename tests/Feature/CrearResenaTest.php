<?php

declare(strict_types=1);

use App\Actions\CrearResena;
use App\DatosTransferencia\DatosNuevaResena;
use App\Enums\EstadoCitaValor;
use App\Events\ResenaCreada;
use App\Exceptions\ResenaNoElegibleException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

/**
 * @return array{negocio: Negocio, profesional: Profesional, cliente: Cliente, cita: Cita}
 */
function negocioConCitaCompletada(): array
{
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Completada,
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('se puede crear una reseña para una cita completada', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita] = negocioConCitaCompletada();

    $resena = app(CrearResena::class)->ejecutar(new DatosNuevaResena(
        cita: $cita,
        calificacion: 5,
        comentario: 'Excelente atención, quedé muy feliz.',
    ));

    expect($resena->negocio_id)->toBe($negocio->id);
    expect($resena->profesional_id)->toBe($profesional->id);
    expect($resena->cliente_id)->toBe($cliente->id);
    expect($resena->calificacion)->toBe(5);
    expect($resena->estaPublicada())->toBeTrue();
});

test('no se puede reseñar una cita que no esta completada', function (): void {
    ['cita' => $cita] = negocioConCitaCompletada();
    $cita->update(['estado' => EstadoCitaValor::Confirmada]);

    $ejecutar = fn () => app(CrearResena::class)->ejecutar(new DatosNuevaResena(
        cita: $cita,
        calificacion: 5,
        comentario: 'Comentario cualquiera.',
    ));

    expect($ejecutar)->toThrow(ResenaNoElegibleException::class);
});

test('no se puede dejar una segunda reseña para la misma cita', function (): void {
    ['cita' => $cita] = negocioConCitaCompletada();

    app(CrearResena::class)->ejecutar(new DatosNuevaResena($cita, 5, 'Primera reseña, bastante contenta.'));

    $ejecutar = fn () => app(CrearResena::class)->ejecutar(new DatosNuevaResena($cita, 1, 'Segunda reseña, no debería pasar.'));

    expect($ejecutar)->toThrow(ResenaNoElegibleException::class);
    expect(Resena::query()->where('cita_id', $cita->id)->count())->toBe(1);
});

test('no se puede reseñar una cita completada hace mas de 60 dias', function (): void {
    ['cita' => $cita] = negocioConCitaCompletada();
    CarbonImmutable::setTestNow($cita->fin->addDays(61));

    $ejecutar = fn () => app(CrearResena::class)->ejecutar(new DatosNuevaResena($cita, 4, 'Comentario tarde.'));

    expect($ejecutar)->toThrow(ResenaNoElegibleException::class);
});

test('crear una reseña dispara el evento ResenaCreada', function (): void {
    Event::fake();
    ['cita' => $cita] = negocioConCitaCompletada();

    app(CrearResena::class)->ejecutar(new DatosNuevaResena($cita, 5, 'Muy buena experiencia, lo recomiendo.'));

    Event::assertDispatched(ResenaCreada::class);
});
