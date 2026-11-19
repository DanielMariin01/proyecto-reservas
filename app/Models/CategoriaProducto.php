<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\CategoriaProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CategoriaProducto extends Model
{
    /** @use HasFactory<CategoriaProductoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'categorias_producto';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'nombre',
        'orden',
    ];

    /**
     * @return HasMany<Producto, $this>
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
