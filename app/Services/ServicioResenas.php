<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoResenaValor;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;
use App\Resenas\CadenaReglasResena;
use Illuminate\Support\Facades\DB;

final class ServicioResenas
{
    public function __construct(
        private readonly CadenaReglasResena $cadenaReglas,
    ) {}

    public function puedeDejarResena(Cita $cita): bool
    {
        return $this->cadenaReglas->permite($cita);
    }

    /**
     * Recalcula los promedios cacheados en Negocio y Profesional a partir
     * de las reseñas publicadas: se recalcula desde cero en vez de sumar o
     * restar el delta, porque es una sola consulta de agregación barata y
     * queda inmune a cualquier desajuste acumulado.
     */
    public function recalcularPromedios(Resena $resena): void
    {
        $this->recalcularParaNegocio($resena->negocio_id);
        $this->recalcularParaProfesional($resena->profesional_id);
    }

    private function recalcularParaNegocio(int $negocioId): void
    {
        [$promedio, $total] = $this->promedioYTotal('negocio_id', $negocioId);

        Negocio::whereKey($negocioId)->update([
            'promedio_calificacion' => $promedio,
            'total_resenas' => $total,
        ]);
    }

    private function recalcularParaProfesional(int $profesionalId): void
    {
        [$promedio, $total] = $this->promedioYTotal('profesional_id', $profesionalId);

        Profesional::whereKey($profesionalId)->update([
            'promedio_calificacion' => $promedio,
            'total_resenas' => $total,
        ]);
    }

    /**
     * @return array{0: float|null, 1: int}
     */
    private function promedioYTotal(string $columna, int $valor): array
    {
        $agregado = DB::table('resenas')
            ->where($columna, $valor)
            ->where('estado', EstadoResenaValor::Publicada->value)
            ->selectRaw('avg(calificacion) as promedio, count(*) as total')
            ->first();

        $total = $agregado === null ? 0 : (int) $agregado->total;
        $promedio = $total > 0 ? round((float) $agregado->promedio, 2) : null;

        return [$promedio, $total];
    }
}
