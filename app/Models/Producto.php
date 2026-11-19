<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'productos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'categoria_producto_id',
        'nombre',
        'descripcion',
        'foto',
        'precio',
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
     * @return BelongsTo<CategoriaProducto, $this>
     */
    public function categoriaProducto(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class);
    }

    /**
     * @return HasMany<ItemPedido, $this>
     */
    public function itemsPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }
}
