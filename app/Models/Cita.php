<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoCitaValor;
use App\Enums\OrigenCita;
use App\Estados\Cita\EstadoCita;
use App\Estados\Cita\FabricaEstadoCita;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\CitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Cita extends Model
{
    /** @use HasFactory<CitaFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'citas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'profesional_id',
        'servicio_id',
        'cliente_id',
        'inicio',
        'fin',
        'estado',
        'precio',
        'origen',
        'notas',
        'motivo_cancelacion',
        'token_cancelacion',
        'veces_reprogramada',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inicio' => 'immutable_datetime',
            'fin' => 'immutable_datetime',
            'estado' => EstadoCitaValor::class,
            'origen' => OrigenCita::class,
        ];
    }

    /**
     * @return BelongsTo<Profesional, $this>
     */
    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function obtenerEstado(): EstadoCita
    {
        return app(FabricaEstadoCita::class)->paraValor($this->estado);
    }

    public function estaActiva(): bool
    {
        return $this->obtenerEstado()->estaActiva();
    }
}
