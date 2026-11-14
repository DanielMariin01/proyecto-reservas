<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            // nombre_producto y precio_unitario son una copia del producto al
            // momento de la compra (igual que citas.precio no relee el precio
            // actual del servicio): si el producto cambia de nombre/precio o
            // se borra despues, el pedido sigue mostrando lo que el cliente
            // realmente compro.
            $table->string('nombre_producto');
            $table->unsignedInteger('precio_unitario');
            $table->unsignedSmallInteger('cantidad');
            $table->unsignedInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_pedido');
    }
};
