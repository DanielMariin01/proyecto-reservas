<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoBloqueo;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\BloqueoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Bloqueo extends Model
{
    /** @use HasFactory<BloqueoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'bloqueos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'profesional_id',
        'tipo',
        'inicio',
        'fin',
        'motivo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoBloqueo::class,
            'inicio' => 'immutable_datetime',
            'fin' => 'immutable_datetime',
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
