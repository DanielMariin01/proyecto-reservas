<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\GastoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Gasto extends Model
{
    /** @use HasFactory<GastoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'gastos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'monto',
        'descripcion',
        'fecha',
        'registrado_por_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'integer',
            'fecha' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_id');
    }
}
