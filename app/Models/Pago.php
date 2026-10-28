<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MetodoPago;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\PagoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Pago extends Model
{
    /** @use HasFactory<PagoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'pagos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'cita_id',
        'metodo',
        'valor',
        'propina',
        'registrado_por_id',
        'anulado_en',
        'motivo_anulacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metodo' => MetodoPago::class,
            'valor' => 'integer',
            'propina' => 'integer',
            'anulado_en' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Cita, $this>
     */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_id');
    }

    public function estaActivo(): bool
    {
        return $this->anulado_en === null;
    }
}
