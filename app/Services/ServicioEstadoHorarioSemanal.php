<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DiaSemana;
use App\Models\Profesional;
use Illuminate\Support\Collection;

/**
 * Convierte entre la forma en que se guarda el horario semanal (filas
 * HorarioTrabajo por profesional/día/rango) y la forma que consume la
 * pantalla de edición (un estado por cada día de App\Enums\DiaSemana,
 * con sus rangos) — compartido entre ProfesionalHorarioController (el
 * dueño edita el de otro) y MiHorarioController (el profesional edita
 * el propio), para no duplicar esta conversión dos veces.
 */
final class ServicioEstadoHorarioSemanal
{
    /**
     * @return array<int, array{disponible: bool, rangos: list<array{hora_inicio: string, hora_fin: string}>}>
     */
    public function agruparPorDia(Profesional $profesional): array
    {
        $horariosPorDia = $profesional->horariosTrabajo()->orderBy('hora_inicio')->get()->groupBy('dia_semana');

        $estado = [];

        foreach (DiaSemana::cases() as $dia) {
            $horarios = $horariosPorDia->get($dia->value, Collection::make());

            $rangos = [];

            foreach ($horarios as $horario) {
                $rangos[] = [
                    'hora_inicio' => mb_substr((string) $horario->hora_inicio, 0, 5),
                    'hora_fin' => mb_substr((string) $horario->hora_fin, 0, 5),
                ];
            }

            $estado[$dia->value] = [
                'disponible' => $horarios->isNotEmpty(),
                'rangos' => $rangos,
            ];
        }

        return $estado;
    }

    /**
     * @param  array<string, array{disponible?: bool, rangos?: list<array{hora_inicio: string, hora_fin: string}>}>  $dias
     * @return array<int, list<array{hora_inicio: string, hora_fin: string}>>
     */
    public function aRangosPorDia(array $dias): array
    {
        $rangosPorDia = [];

        foreach ($dias as $diaValor => $datosDelDia) {
            if (! ($datosDelDia['disponible'] ?? false)) {
                continue;
            }

            $rangos = $datosDelDia['rangos'] ?? [];

            if ($rangos === []) {
                continue;
            }

            $rangosPorDia[(int) $diaValor] = $rangos;
        }

        return $rangosPorDia;
    }
}
