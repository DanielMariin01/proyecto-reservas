<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PagoSuscripcionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PagoSuscripcion extends Model
{
    /** @use HasFactory<PagoSuscripcionFactory> */
    use HasFactory;

    protected $table = 'pagos_suscripcion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'fecha',
        'valor',
        'metodo',
        'periodo_desde',
        'periodo_hasta',
        'registrado_por_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'periodo_desde' => 'date',
            'periodo_hasta' => 'date',
            'valor' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Negocio, $this>
     */
    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_id');
    }
}
