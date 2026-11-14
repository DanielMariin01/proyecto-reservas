<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPedidoValor;
use App\Enums\TipoEntregaValor;
use App\Estados\Pedido\EstadoPedido;
use App\Estados\Pedido\FabricaEstadoPedido;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'pedidos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'cliente_id',
        'estado',
        'tipo_entrega',
        'barrio',
        'nota',
        'total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado' => EstadoPedidoValor::class,
            'tipo_entrega' => TipoEntregaValor::class,
        ];
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return HasMany<ItemPedido, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    /**
     * @return HasMany<Pago, $this>
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function obtenerEstado(): EstadoPedido
    {
        return app(FabricaEstadoPedido::class)->paraValor($this->estado);
    }
}
