<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiaSemana;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\HorarioTrabajoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HorarioTrabajo extends Model
{
    /** @use HasFactory<HorarioTrabajoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'horarios_trabajo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'profesional_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dia_semana' => DiaSemana::class,
        ];
    }

    /**
     * @return BelongsTo<Profesional, $this>
     */
    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }
}
