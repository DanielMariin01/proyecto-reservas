<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\CategoriaServicioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CategoriaServicio extends Model
{
    /** @use HasFactory<CategoriaServicioFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'categorias_servicio';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'nombre',
        'orden',
    ];

    /**
     * @return HasMany<Servicio, $this>
     */
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }
}
