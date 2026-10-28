<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosHorarioSemanal;
use App\Exceptions\RangosDeHorarioSuperpuestosException;
use App\Models\HorarioTrabajo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reemplaza todo el horario semanal del profesional de una sola vez,
 * en vez de editar fila por fila, porque la pantalla de edición visual
 * envía siempre el horario completo.
 */
final class GuardarHorarioSemanal
{
    public function ejecutar(DatosHorarioSemanal $datos): void
    {
        $this->validarSinSolapes($datos->rangosPorDia);

        DB::transaction(function () use ($datos): void {
            HorarioTrabajo::query()
                ->where('profesional_id', $datos->profesional->id)
                ->delete();

            foreach ($datos->rangosPorDia as $diaValor => $rangos) {
                foreach ($rangos as $rango) {
                    HorarioTrabajo::create([
                        'negocio_id' => $datos->profesional->negocio_id,
                        'profesional_id' => $datos->profesional->id,
                        'dia_semana' => $diaValor,
                        'hora_inicio' => $rango['hora_inicio'],
                        'hora_fin' => $rango['hora_fin'],
                    ]);
                }
            }
        });
    }

    /**
     * @param  array<int, list<array{hora_inicio: string, hora_fin: string}>>  $rangosPorDia
     */
    private function validarSinSolapes(array $rangosPorDia): void
    {
        foreach ($rangosPorDia as $rangos) {
            $ordenados = Collection::make($rangos)->sortBy('hora_inicio')->values()->all();

            for ($i = 1; $i < count($ordenados); $i++) {
                if ($ordenados[$i]['hora_inicio'] < $ordenados[$i - 1]['hora_fin']) {
                    throw new RangosDeHorarioSuperpuestosException;
                }
            }
        }
    }
}
