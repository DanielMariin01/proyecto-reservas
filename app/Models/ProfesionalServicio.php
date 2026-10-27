<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int|null $precio
 * @property int|null $duracion_minutos
 */
final class ProfesionalServicio extends Pivot
{
    protected $table = 'profesional_servicio';
}
