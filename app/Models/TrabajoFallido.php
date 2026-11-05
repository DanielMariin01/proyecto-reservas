<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Envuelve la tabla `failed_jobs` que Laravel ya crea (sin migración
 * propia, la tabla no es nuestra). RNF-OBS-03: el panel de salud lista
 * estos trabajos (incluidos los correos encolados que fallaron, ya que
 * en este proyecto todas las notificaciones por correo son
 * App\Notifications\* con ShouldQueue) con opción de reintentar.
 */
final class TrabajoFallido extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function tipo(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->esCorreo() ? 'Correo' : 'Otro',
        );
    }

    private function esCorreo(): bool
    {
        $datos = json_decode($this->payload, true);
        $nombreDelJob = is_array($datos) ? (string) ($datos['displayName'] ?? '') : '';

        return str_contains($nombreDelJob, 'SendQueuedNotifications') || str_contains($nombreDelJob, 'SendQueuedMailable');
    }
}
