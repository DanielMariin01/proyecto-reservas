<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ServicioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Servicio extends Model
{
    /** @use HasFactory<ServicioFactory> */
    use HasFactory;

    use PerteneceANegocio;

    public const DURACION_MINIMA_MINUTOS = 5;

    protected $table = 'servicios';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'categoria_servicio_id',
        'nombre',
        'descripcion',
        'foto',
        'duracion_minutos',
        'precio',
        'margen_antes_minutos',
        'margen_despues_minutos',
        'intervalo_franjas_minutos',
        'aviso_minimo_minutos',
        'visible',
        'orden',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CategoriaServicio, $this>
     */
    public function categoriaServicio(): BelongsTo
    {
        return $this->belongsTo(CategoriaServicio::class);
    }

    /**
     * @return BelongsToMany<Profesional, $this, ProfesionalServicio>
     */
    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'profesional_servicio')
            ->using(ProfesionalServicio::class)
            ->withPivot(['precio', 'duracion_minutos'])
            ->withTimestamps();
    }

    /**
     * RF-SER-03 / sección 5: el margen propio del servicio sobrescribe el
     * del negocio.
     */
    public function margenAntesMinutos(): int
    {
        return $this->margen_antes_minutos ?? $this->negocioPropietario()->margen_antes_minutos;
    }

    public function margenDespuesMinutos(): int
    {
        return $this->margen_despues_minutos ?? $this->negocioPropietario()->margen_despues_minutos;
    }

    /**
     * Sección 5: servicio -> negocio -> igual a la duración del servicio.
     */
    public function intervaloFranjasMinutos(): int
    {
        return $this->intervalo_franjas_minutos
            ?? $this->negocioPropietario()->intervalo_franjas_minutos
            ?? $this->duracion_minutos;
    }

    public function avisoMinimoMinutos(): int
    {
        return $this->aviso_minimo_minutos ?? $this->negocioPropietario()->aviso_minimo_minutos;
    }

    public function duracionTotalMinutos(): int
    {
        return $this->margenAntesMinutos() + $this->duracion_minutos + $this->margenDespuesMinutos();
    }

    private function negocioPropietario(): Negocio
    {
        return Negocio::findOrFail($this->negocio_id);
    }
}
