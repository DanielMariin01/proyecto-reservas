<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ItemPedidoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ItemPedido extends Model
{
    /** @use HasFactory<ItemPedidoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'items_pedido';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'pedido_id',
        'producto_id',
        'nombre_producto',
        'precio_unitario',
        'cantidad',
        'subtotal',
    ];

    /**
     * @return BelongsTo<Pedido, $this>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
