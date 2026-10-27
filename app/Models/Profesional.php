<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ProfesionalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

final class Profesional extends Model
{
    /** @use HasFactory<ProfesionalFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'profesionales';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'usuario_id',
        'nombre',
        'foto',
        'especialidad',
        'biografia',
        'certificaciones',
        'color',
        'activo',
        'google_calendar_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * @return BelongsToMany<Servicio, $this, ProfesionalServicio>
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'profesional_servicio')
            ->using(ProfesionalServicio::class)
            ->withPivot(['precio', 'duracion_minutos'])
            ->withTimestamps();
    }

    /**
     * RF-PRO-02: el precio propio del profesional para ese servicio
     * sobrescribe el del servicio cuando está definido.
     */
    public function precioParaServicio(Servicio $servicio): int
    {
        return $this->valorPropioEnPivote($servicio, 'precio') ?? $servicio->precio;
    }

    public function duracionMinutosParaServicio(Servicio $servicio): int
    {
        return $this->valorPropioEnPivote($servicio, 'duracion_minutos') ?? $servicio->duracion_minutos;
    }

    private function valorPropioEnPivote(Servicio $servicio, string $columna): ?int
    {
        $valor = DB::table('profesional_servicio')
            ->where('profesional_id', $this->id)
            ->where('servicio_id', $servicio->id)
            ->value($columna);

        return $valor === null ? null : (int) $valor;
    }
}
