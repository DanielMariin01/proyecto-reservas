<?php

declare(strict_types=1);

use App\Models\RegistroError;
use App\Observabilidad\ContextoObservabilidad;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Events\JobProcessing;

/**
 * Antes de este fix, GenerarCodigoDeReferencia recibía el id_peticion
 * vacío fuera del pipeline HTTP (AsignarIdPeticion solo corre ahí) y
 * producía el código inválido "REF-", sin nada que lo distinga de otro
 * error ni que sirva para cruzarlo con el log.
 */
test('ContextoObservabilidad genera un id de peticion aunque nadie lo haya fijado', function (): void {
    $idPeticion = app(ContextoObservabilidad::class)->idPeticion();

    expect($idPeticion)->not->toBeEmpty();
    expect($idPeticion)->toMatch('/^[0-9a-f-]{36}$/');
});

test('un error reportado fuera de una peticion web recibe un codigo de referencia valido, no REF- vacio', function (): void {
    $excepcion = new RuntimeException('Fallo simulado fuera del pipeline HTTP');

    app()->make(ExceptionHandler::class)->report($excepcion);

    $registro = RegistroError::query()->latest('id')->first();

    expect($registro)->not->toBeNull();
    expect($registro->codigo_referencia)->toMatch('/^REF-[A-Z0-9]{8}$/');
    expect($registro->id_peticion)->not->toBeEmpty();
});

test('cada trabajo de cola recibe su propio id_peticion, no el de la peticion web que lo encolo', function (): void {
    /**
     * Illuminate\Queue\Worker::process() -el que usa de verdad
     * `php artisan queue:work`- dispara JobProcessing antes de ejecutar
     * el trabajo y, si falla, reporta la excepcion dentro de esa misma
     * llamada (Worker::handleJobException() -> $this->exceptions->report()).
     * Se reproduce esa misma secuencia sin levantar un worker completo.
     */
    app(ContextoObservabilidad::class)->definirIdPeticion('id-de-la-peticion-web-que-encolo-el-trabajo');

    event(new JobProcessing('database', new class
    {
        public function getConnectionName(): string
        {
            return 'database';
        }

        public function getQueue(): string
        {
            return 'default';
        }

        public function getJobId(): string
        {
            return 'job-de-prueba';
        }

        public function payload(): array
        {
            return ['displayName' => 'TrabajoDeQa'];
        }
    }));

    $idDentroDelTrabajo = app(ContextoObservabilidad::class)->idPeticion();

    app()->make(ExceptionHandler::class)
        ->report(new RuntimeException('Fallo simulado dentro de un trabajo en cola'));

    $registro = RegistroError::query()->latest('id')->first();

    expect($idDentroDelTrabajo)->not->toBe('id-de-la-peticion-web-que-encolo-el-trabajo');
    expect($registro)->not->toBeNull();
    expect($registro->id_peticion)->toBe($idDentroDelTrabajo);
    expect($registro->codigo_referencia)->toMatch('/^REF-[A-Z0-9]{8}$/');
});
