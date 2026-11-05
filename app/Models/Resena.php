<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoResenaValor;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ResenaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Resena extends Model
{
    /** @use HasFactory<ResenaFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'resenas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'cita_id',
        'cliente_id',
        'profesional_id',
        'calificacion',
        'comentario',
        'estado',
        'respuesta_texto',
        'respondida_en',
        'oculta_en',
        'oculta_por_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado' => EstadoResenaValor::class,
            'calificacion' => 'integer',
            'respondida_en' => 'immutable_datetime',
            'oculta_en' => 'immutable_datetime',
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
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsTo<Profesional, $this>
     */
    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function ocultaPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'oculta_por_id');
    }

    public function estaPublicada(): bool
    {
        return $this->estado === EstadoResenaValor::Publicada;
    }
}
