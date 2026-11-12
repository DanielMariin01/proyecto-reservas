<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

final class Usuario extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    protected $table = 'usuarios';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'password',
        'es_super_admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'es_super_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne<Profesional, $this>
     */
    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Negocio) {
            return false;
        }

        if ($this->negociosDondeTieneRol()->contains('id', $tenant->id)) {
            return true;
        }

        return $this->es_super_admin && $this->negocioSuplantadoId() === $tenant->id;
    }

    /**
     * Negocios donde el usuario tiene al menos un rol asignado (spatie "teams").
     *
     * Consulta la tabla pivote directamente en vez de usar la relación roles()
     * de spatie, porque esa relación ya viene filtrada por el "equipo" (negocio)
     * actualmente activo, y aquí necesitamos verlos todos para resolver cuál
     * activar primero.
     *
     * @return Collection<int, Negocio>
     */
    public function negociosDondeTieneRol(): Collection
    {
        $idsDeNegocio = DB::table('modelos_con_roles')
            ->where('model_id', $this->id)
            ->where('model_type', self::class)
            ->whereNotNull('negocio_id')
            ->distinct()
            ->pluck('negocio_id');

        return Negocio::query()->whereIn('id', $idsDeNegocio)->get();
    }

    public function negocioSuplantadoId(): ?int
    {
        /** @var int|null $id */
        $id = session('negocio_suplantado_id');

        return $id;
    }

    public function estaSuplantandoA(Negocio $negocio): bool
    {
        return $this->es_super_admin && $this->negocioSuplantadoId() === $negocio->id;
    }

    /**
     * @param  Builder<Usuario>  $query
     * @return Builder<Usuario>
     */
    public function scopeDeNegocio(Builder $query, int $negocioId): Builder
    {
        return $query->whereIn('id', function ($subconsulta) use ($negocioId): void {
            $subconsulta->select('model_id')
                ->from('modelos_con_roles')
                ->where('negocio_id', $negocioId)
                ->where('model_type', self::class);
        });
    }
}
