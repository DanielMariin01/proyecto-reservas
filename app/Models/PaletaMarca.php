<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaletaMarcaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo global de paletas de marca (sección "Librería de paletas"):
 * no pertenece a un negocio, varios negocios de cualquier tipo pueden
 * usar la misma. El super admin elige una por negocio en vez de escribir
 * un hex libre.
 */
final class PaletaMarca extends Model
{
    /** @use HasFactory<PaletaMarcaFactory> */
    use HasFactory;

    protected $table = 'paletas_marca';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'color_primario',
        'color_secundario',
    ];

    /**
     * @return HasMany<Negocio, $this>
     */
    public function negocios(): HasMany
    {
        return $this->hasMany(Negocio::class);
    }
}
