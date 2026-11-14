<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\App\RecursoCita;
use App\Models\Cita;
use App\Models\Profesional;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Construye una columna de la agenda visual (horario de trabajo del día +
 * sus citas, en la forma que consume CalendarioFranjasHora) para un
 * profesional puntual. Compartido entre AgendaDelDiaController (una
 * columna por cada profesional del negocio, para el dueño) y
 * MiHorarioController (una sola columna, la propia, para el profesional),
 * para no duplicar esta construcción dos veces.
 */
final class ServicioColumnaAgenda
{
    public function __construct(private readonly ServicioAccionesCita $servicioAccionesCita) {}

    /**
     * @return array{profesionalId: int, profesionalNombre: string, profesionalColor: string|null, profesionalFoto: string|null, horaInicio: string|null, horaFin: string|null, citas: Collection<int, RecursoCita>}
     */
    public function construir(Profesional $profesional, int $diaSemana, CarbonImmutable $inicioDiaUtc, CarbonImmutable $finDiaUtc, Usuario $usuarioActual): array
    {
        $rangosDelDia = $profesional->horariosTrabajo()->where('dia_semana', $diaSemana)->get();

        return [
            'profesionalId' => $profesional->id,
            'profesionalNombre' => $profesional->nombre,
            'profesionalColor' => $profesional->color,
            'profesionalFoto' => $profesional->foto !== null ? Storage::disk('public')->url($profesional->foto) : null,
            'horaInicio' => $rangosDelDia->isEmpty() ? null : mb_substr((string) $rangosDelDia->min('hora_inicio'), 0, 5),
            'horaFin' => $rangosDelDia->isEmpty() ? null : mb_substr((string) $rangosDelDia->max('hora_fin'), 0, 5),
            'citas' => Cita::query()
                ->where('profesional_id', $profesional->id)
                ->where('inicio', '>=', $inicioDiaUtc)
                ->where('inicio', '<', $finDiaUtc)
                ->orderBy('inicio')
                ->get()
                ->map(fn (Cita $cita): RecursoCita => new RecursoCita($cita, $usuarioActual, $this->servicioAccionesCita)),
        ];
    }
}
