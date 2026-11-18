<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Enums\TipoNegocio;
use App\Models\Cliente;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Negocios\ContextoNegocioActual;

/**
 * La policy nunca compara ella misma el negocio_id de la historia contra el
 * negocio actual (esa parte la cubre el scope global de PerteneceANegocio
 * sobre la consulta que trae el modelo, igual que el resto del sistema —
 * ver CategoriaServicioControllerTest). Aquí se fija el tenant a mano con
 * ContextoNegocioActual porque todavía no hay rutas/controlador (eso es la
 * siguiente tarea) que lo fijen por la ruta.
 */
function entrarAlNegocio(Negocio $negocio): void
{
    app(ContextoNegocioActual::class)->fijar($negocio);
}

function crearHistoriaClinicaDeNegocio(Negocio $negocio, ?Profesional $profesional = null): HistoriaClinica
{
    $profesional ??= Profesional::factory()->create(['negocio_id' => $negocio->id]);

    return HistoriaClinica::factory()->create([
        'negocio_id' => $negocio->id,
        'cliente_id' => Cliente::factory()->create(['negocio_id' => $negocio->id])->id,
        'profesional_id' => $profesional->id,
    ]);
}

test('un negocio que no requiere historia clinica bloquea el acceso aunque el usuario tenga el permiso', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Barberia]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $historia = crearHistoriaClinicaDeNegocio($negocio);
    entrarAlNegocio($negocio);

    expect($dueno->can('viewAny', HistoriaClinica::class))->toBeFalse();
    expect($dueno->can('view', $historia))->toBeFalse();
    expect($dueno->can('create', HistoriaClinica::class))->toBeFalse();
});

test('el dueno de un spa ve y gestiona todas las historias del negocio', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $historiaDeOtroProfesional = crearHistoriaClinicaDeNegocio($negocio);
    entrarAlNegocio($negocio);

    expect($dueno->can('viewAny', HistoriaClinica::class))->toBeTrue();
    expect($dueno->can('view', $historiaDeOtroProfesional))->toBeTrue();
    expect($dueno->can('update', $historiaDeOtroProfesional))->toBeTrue();
    expect($dueno->can('delete', $historiaDeOtroProfesional))->toBeTrue();
});

test('recepcion no puede ver ni gestionar historias clinicas aunque administre clientes', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);
    $historia = crearHistoriaClinicaDeNegocio($negocio);
    entrarAlNegocio($negocio);

    expect($recepcion->can('viewAny', HistoriaClinica::class))->toBeFalse();
    expect($recepcion->can('view', $historia))->toBeFalse();
    expect($recepcion->can('create', HistoriaClinica::class))->toBeFalse();
});

test('un profesional solo ve y gestiona las historias clinicas que el mismo atendio', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $usuarioProfesional->id]);
    $historiaPropia = crearHistoriaClinicaDeNegocio($negocio, $profesional);
    $historiaDeOtro = crearHistoriaClinicaDeNegocio($negocio);
    entrarAlNegocio($negocio);

    expect($usuarioProfesional->can('viewAny', HistoriaClinica::class))->toBeTrue();
    expect($usuarioProfesional->can('view', $historiaPropia))->toBeTrue();
    expect($usuarioProfesional->can('update', $historiaPropia))->toBeTrue();
    expect($usuarioProfesional->can('view', $historiaDeOtro))->toBeFalse();
    expect($usuarioProfesional->can('update', $historiaDeOtro))->toBeFalse();
    expect($usuarioProfesional->can('delete', $historiaDeOtro))->toBeFalse();
});

test('el scope global filtra las historias clinicas por el negocio actual', function (): void {
    $negocioA = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $negocioB = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    crearHistoriaClinicaDeNegocio($negocioA);
    crearHistoriaClinicaDeNegocio($negocioB);

    entrarAlNegocio($negocioA);

    expect(HistoriaClinica::query()->count())->toBe(1);
    expect(HistoriaClinica::query()->first()->negocio_id)->toBe($negocioA->id);
});
