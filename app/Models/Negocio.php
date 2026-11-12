<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoSuscripcion;
use App\Enums\RolNegocio;
use App\Enums\TipoNegocio;
use App\Negocios\ContextoNegocioActual;
use Carbon\Carbon;
use Database\Factories\NegocioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class Negocio extends Model
{
    /** @use HasFactory<NegocioFactory> */
    use HasFactory;

    protected $table = 'negocios';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'logo',
        'paleta_marca_id',
        'direccion',
        'ciudad',
        'telefono',
        'whatsapp',
        'redes_sociales',
        'zona_horaria',
        'estado_suscripcion',
        'proxima_fecha_pago',
        'dias_gracia',
        'meses_para_cancelar',
        'meses_retencion_datos',
        'vencido_desde',
        'suspendido_desde',
        'cancelado_desde',
        'aviso_minimo_minutos',
        'ventana_maxima_dias',
        'margen_antes_minutos',
        'margen_despues_minutos',
        'intervalo_franjas_minutos',
        'horas_minimas_cancelacion',
        'horas_minimas_reprogramacion',
        'maximo_reprogramaciones',
        'confirmacion_manual',
        'minutos_tolerancia_inasistencia',
        'permite_editar_pagina',
        'promedio_calificacion',
        'total_resenas',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoNegocio::class,
            'estado_suscripcion' => EstadoSuscripcion::class,
            'redes_sociales' => 'array',
            'proxima_fecha_pago' => 'date',
            'vencido_desde' => 'datetime',
            'suspendido_desde' => 'datetime',
            'cancelado_desde' => 'datetime',
            'confirmacion_manual' => 'boolean',
            'permite_editar_pagina' => 'boolean',
            'promedio_calificacion' => 'float',
        ];
    }

    /**
     * @return HasMany<PagoSuscripcion, $this>
     */
    public function pagosSuscripcion(): HasMany
    {
        return $this->hasMany(PagoSuscripcion::class);
    }

    /**
     * @return HasOne<Pagina, $this>
     */
    public function pagina(): HasOne
    {
        return $this->hasOne(Pagina::class);
    }

    /**
     * @return BelongsTo<PaletaMarca, $this>
     */
    public function paletaMarca(): BelongsTo
    {
        return $this->belongsTo(PaletaMarca::class);
    }

    /**
     * @return HasMany<RegistroAuditoria, $this>
     */
    public function registrosAuditoria(): HasMany
    {
        return $this->hasMany(RegistroAuditoria::class);
    }

    /**
     * @return HasMany<Resena, $this>
     */
    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class);
    }

    /**
     * El negocio (tenant) activo en la petición actual. Solo se usa en
     * puntos del código que únicamente se ejecutan dentro de una ruta ya
     * resuelta a un tenant, fijado por `ResolverNegocioDesdeRuta` en
     * `ContextoNegocioActual`.
     */
    public static function actual(): self
    {
        $negocio = self::actualOSinNegocio();

        if (! $negocio instanceof self) {
            throw new RuntimeException('No hay un negocio activo en el panel.');
        }

        return $negocio;
    }

    public static function actualOSinNegocio(): ?self
    {
        return app(ContextoNegocioActual::class)->actual();
    }

    public function estaOperativo(): bool
    {
        return $this->estado_suscripcion->permiteAccesoAlPanel();
    }

    /**
     * RF-SUS-04: un negocio suspendido o cancelado no recibe reservas
     * nuevas, tampoco por API.
     */
    public function aceptaReservasPublicas(): bool
    {
        return $this->estado_suscripcion->permiteReservas();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function fechaLimiteRetencionDeDatos(): ?Carbon
    {
        if ($this->cancelado_desde === null) {
            return null;
        }

        return $this->cancelado_desde->copy()->addMonths($this->meses_retencion_datos);
    }

    /**
     * Usuarios con al menos un rol en este negocio, con el nombre del rol.
     *
     * @return Collection<int, array{usuario: Usuario, rol: string}>
     */
    public function usuariosConRol(): Collection
    {
        return DB::table('modelos_con_roles')
            ->join('roles', 'roles.id', '=', 'modelos_con_roles.role_id')
            ->where('modelos_con_roles.negocio_id', $this->id)
            ->where('modelos_con_roles.model_type', Usuario::class)
            ->get(['modelos_con_roles.model_id as usuario_id', 'roles.name as rol'])
            ->map(fn ($fila) => [
                'usuario' => Usuario::find((int) $fila->usuario_id),
                'rol' => (string) $fila->rol,
            ])
            ->filter(fn (array $fila) => $fila['usuario'] !== null)
            ->values();
    }

    /**
     * @return Collection<int, Usuario>
     */
    public function duenos(): Collection
    {
        return $this->usuariosConRol()
            ->filter(fn (array $fila) => $fila['rol'] === RolNegocio::Dueno->value)
            ->map(fn (array $fila) => $fila['usuario'])
            ->values();
    }
}
