<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Enums\TipoNegocio;
use App\Models\Cliente;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Support\Facades\Storage;

const FIRMA_PNG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function datosFormularioHistoriaClinica(array $sobrescribir = []): array
{
    return array_merge([
        'profesional_id' => 0,
        'fecha' => now()->toDateString(),
        'alergias' => false,
        'reaccion_anterior' => false,
        'diabetes' => false,
        'problemas_circulacion' => false,
        'medicamentos' => false,
        'heridas_actuales' => false,
        'otra_condicion' => null,
        'detalle_reaccion' => null,
        'piel' => ['normal'],
        'una' => ['sin_alteraciones'],
        'hallazgos_visibles' => [],
        'hallazgo_otro_texto' => null,
        'observaciones_valoracion' => null,
        'decision' => 'se_realiza',
        'motivo_decision' => null,
        'servicio_diseno' => 'semipermanente',
        'servicio_diseno_otro_texto' => null,
        'forma_una' => 'almendrada',
        'largo_una' => 'medio',
        'colores' => null,
        'decoracion_referencia' => null,
        'hora_inicio' => '10:00',
        'hora_final' => '11:00',
        'tecnica_realizada' => null,
        'productos_marcas' => null,
        'lote_vencimiento' => null,
        'retiro_previo' => false,
        'reparaciones' => false,
        'reparaciones_detalle' => null,
        'cambios_durante_servicio' => null,
        'novedades_incidentes' => null,
        'valor' => 40000,
        'abono' => 0,
        'saldo' => 40000,
        'revision_resultado' => false,
        'recibio_cuidados' => false,
        'fecha_seguimiento' => null,
        'proximo_mantenimiento_sugerido' => null,
        'novedad_pendiente' => false,
        'consentimiento_aceptado' => true,
        'autorizacion_fotos' => 'solo_historial_privado',
        'firma_clienta_base64' => FIRMA_PNG_BASE64,
        'firma_profesional_base64' => FIRMA_PNG_BASE64,
    ], $sobrescribir);
}

test('un negocio que no es spa no deja crear ni ver historias clinicas', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Barberia]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/clientes/{$cliente->id}/historias-clinicas/create")->assertForbidden();
    $this->actingAs($dueno)
        ->postJson(
            "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas",
            datosFormularioHistoriaClinica(['profesional_id' => $profesional->id]),
        )
        ->assertForbidden();
});

test('el dueno de un spa crea un registro de historia clinica con sus firmas', function (): void {
    Storage::fake('local');
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->postJson(
        "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas",
        datosFormularioHistoriaClinica(['profesional_id' => $profesional->id]),
    );

    $respuesta->assertCreated();
    $this->assertDatabaseHas('historias_clinicas', [
        'negocio_id' => $negocio->id,
        'cliente_id' => $cliente->id,
        'profesional_id' => $profesional->id,
    ]);
    $this->assertDatabaseHas('registros_auditoria', ['accion' => 'historia_clinica.registrada', 'negocio_id' => $negocio->id]);

    $historia = HistoriaClinica::query()->first();
    Storage::disk('local')->assertExists($historia->firma_clienta_path);
    Storage::disk('local')->assertExists($historia->firma_profesional_path);
});

test('GET historias clinicas devuelve hora_inicio y hora_final sin segundos', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    // MariaDB normaliza cualquier `time` a HH:MM:SS al guardarlo, sin
    // importar el formato con el que se escribio - por eso la columna
    // siempre vuelve con segundos si no se normaliza en la API.
    HistoriaClinica::factory()->create([
        'negocio_id' => $negocio->id,
        'cliente_id' => $cliente->id,
        'hora_inicio' => '10:00',
        'hora_final' => '11:15',
    ]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas");

    $respuesta->assertOk();
    expect($respuesta->json('data.0.horaInicio'))->toBe('10:00');
    expect($respuesta->json('data.0.horaFinal'))->toBe('11:15');
});

test('reenviar sin cambios las horas que trae la API al editar no falla la validacion', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $creacion = $this->actingAs($dueno)->postJson(
        "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas",
        datosFormularioHistoriaClinica(['profesional_id' => $profesional->id, 'hora_inicio' => '10:00', 'hora_final' => '11:15']),
    );
    $historiaId = $creacion->json('data.id');

    // Esto es exactamente lo que hace el formulario de edicion: cargar
    // los valores que devuelve la API y reenviarlos sin tocarlos.
    $cargaParaEditar = $this->actingAs($dueno)
        ->getJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas")
        ->json('data.0');

    $respuesta = $this->actingAs($dueno)->putJson(
        "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas/{$historiaId}",
        datosFormularioHistoriaClinica([
            'profesional_id' => $profesional->id,
            'hora_inicio' => $cargaParaEditar['horaInicio'],
            'hora_final' => $cargaParaEditar['horaFinal'],
        ]),
    );

    $respuesta->assertOk();
});

test('hora final antes que hora inicio responde con el mensaje de hora, no de fecha', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->postJson(
        "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas",
        datosFormularioHistoriaClinica(['profesional_id' => $profesional->id, 'hora_inicio' => '11:00', 'hora_final' => '10:00']),
    );

    $respuesta->assertUnprocessable();
    expect($respuesta->json('errors.hora_final.0'))->toBe('El campo hora final debe ser una hora posterior a hora de inicio.');
});

test('recepcion no puede crear, ver ni editar historias clinicas aunque administre clientes', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $historia = HistoriaClinica::factory()->create([
        'negocio_id' => $negocio->id,
        'cliente_id' => $cliente->id,
        'profesional_id' => $profesional->id,
    ]);

    $this->actingAs($recepcion)->getJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas")->assertForbidden();
    $this->actingAs($recepcion)
        ->postJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas", datosFormularioHistoriaClinica(['profesional_id' => $profesional->id]))
        ->assertForbidden();
    $this->actingAs($recepcion)
        ->getJson("/app/{$negocio->slug}/api/historias-clinicas/{$historia->id}/firma-clienta")
        ->assertForbidden();
});

test('un profesional solo puede actualizar o eliminar las historias clinicas que el mismo atendio', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesionalPropio = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $historiaPropia = HistoriaClinica::factory()->create([
        'negocio_id' => $negocio->id,
        'cliente_id' => $cliente->id,
        'profesional_id' => $profesionalPropio->id,
    ]);
    $historiaDeOtro = HistoriaClinica::factory()->create([
        'negocio_id' => $negocio->id,
        'cliente_id' => $cliente->id,
        'profesional_id' => $otroProfesional->id,
    ]);

    $this->actingAs($usuarioProfesional)
        ->putJson(
            "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas/{$historiaPropia->id}",
            datosFormularioHistoriaClinica(['profesional_id' => $profesionalPropio->id, 'valor' => 50000]),
        )
        ->assertOk();

    $this->actingAs($usuarioProfesional)
        ->putJson(
            "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas/{$historiaDeOtro->id}",
            datosFormularioHistoriaClinica(['profesional_id' => $otroProfesional->id]),
        )
        ->assertForbidden();

    $this->actingAs($usuarioProfesional)
        ->deleteJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas/{$historiaDeOtro->id}")
        ->assertForbidden();
});

test('la lista de historias clinicas de un profesional no insinua las de sus companeras', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesionalPropio = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    HistoriaClinica::factory()->create(['negocio_id' => $negocio->id, 'cliente_id' => $cliente->id, 'profesional_id' => $profesionalPropio->id]);
    HistoriaClinica::factory()->create(['negocio_id' => $negocio->id, 'cliente_id' => $cliente->id, 'profesional_id' => $otroProfesional->id]);

    $respuestaProfesional = $this->actingAs($usuarioProfesional)
        ->getJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas");
    $respuestaProfesional->assertOk();
    expect($respuestaProfesional->json('data'))->toHaveCount(1);
    expect($respuestaProfesional->json('data.0.profesionalId'))->toBe($profesionalPropio->id);

    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $respuestaDueno = $this->actingAs($dueno)
        ->getJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas");
    $respuestaDueno->assertOk();
    expect($respuestaDueno->json('data'))->toHaveCount(2);
});

test('un dueno no puede ver ni editar una historia clinica de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $negocioB = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $clienteA = Cliente::factory()->create(['negocio_id' => $negocioA->id]);
    $profesionalA = Profesional::factory()->create(['negocio_id' => $negocioA->id]);
    $historiaB = HistoriaClinica::factory()->create(['negocio_id' => $negocioB->id]);

    $this->actingAs($duenoA)
        ->putJson(
            "/app/{$negocioA->slug}/api/clientes/{$clienteA->id}/historias-clinicas/{$historiaB->id}",
            datosFormularioHistoriaClinica(['profesional_id' => $profesionalA->id]),
        )
        ->assertNotFound();
});

test('eliminar una historia clinica borra tambien sus firmas del disco', function (): void {
    Storage::fake('local');
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Spa]);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $creacion = $this->actingAs($dueno)->postJson(
        "/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas",
        datosFormularioHistoriaClinica(['profesional_id' => $profesional->id]),
    );
    $historiaId = $creacion->json('data.id');
    $historia = HistoriaClinica::query()->findOrFail($historiaId);
    $rutaFirma = $historia->firma_clienta_path;

    $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/clientes/{$cliente->id}/historias-clinicas/{$historiaId}")->assertOk();

    expect(HistoriaClinica::query()->find($historiaId))->toBeNull();
    Storage::disk('local')->assertMissing($rutaFirma);
});
