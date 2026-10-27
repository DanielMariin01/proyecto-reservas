<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

final class Usuario extends Authenticatable implements FilamentUser, HasDefaultTenant, HasName, HasTenants, MustVerifyEmail
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

    public function getFilamentName(): string
    {
        return $this->nombre;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->es_super_admin;
        }

        return $this->es_super_admin || $this->negociosDondeTieneRol()->isNotEmpty();
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
     * @return Collection<int, Negocio>
     */
    public function getTenants(Panel $panel): Collection
    {
        $negocios = $this->negociosDondeTieneRol();

        $suplantadoId = $this->negocioSuplantadoId();

        if ($this->es_super_admin && $suplantadoId !== null && ! $negocios->contains('id', $suplantadoId)) {
            $negocioSuplantado = Negocio::find($suplantadoId);

            if ($negocioSuplantado !== null) {
                $negocios->push($negocioSuplantado);
            }
        }

        return $negocios;
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        $negocios = $this->getTenants($panel);

        return $negocios->count() === 1 ? $negocios->first() : null;
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
