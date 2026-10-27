<?php

declare(strict_types=1);

use App\Actions\CambiarEstadoNegocio;
use App\Actions\SalirDeSuplantacion;
use App\Actions\SuplantarNegocio;
use App\DatosTransferencia\DatosCambioEstadoNegocio;
use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Models\RegistroAuditoria;
use App\Models\Usuario;

test('el super admin puede entrar como un negocio del que no es miembro', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    app(SuplantarNegocio::class)->ejecutar($negocio, $superAdmin);

    $respuesta = $this->actingAs($superAdmin)
        ->withSession(['negocio_suplantado_id' => $negocio->id])
        ->get("/app/{$negocio->slug}");

    $respuesta->assertOk();
    $respuesta->assertSee($negocio->nombre);
});

test('las acciones hechas durante una suplantacion quedan marcadas como hechas por soporte', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $this->withSession(['negocio_suplantado_id' => $negocio->id]);

    app(CambiarEstadoNegocio::class)->ejecutar(new DatosCambioEstadoNegocio(
        negocio: $negocio,
        estadoDestino: EstadoSuscripcion::Suspendido,
        motivo: 'Prueba',
        cambiadoPor: $superAdmin,
    ));

    $registro = RegistroAuditoria::latest('id')->first();

    expect($registro->hecho_por_soporte)->toBeTrue();
});

test('salir de la suplantacion limpia la sesion', function (): void {
    $this->withSession(['negocio_suplantado_id' => 5]);

    app(SalirDeSuplantacion::class)->ejecutar();

    expect(session()->has('negocio_suplantado_id'))->toBeFalse();
});
