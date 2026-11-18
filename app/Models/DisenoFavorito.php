<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DisenoFavoritoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DisenoFavorito extends Model
{
    /** @use HasFactory<DisenoFavoritoFactory> */
    use HasFactory;

    protected $table = 'disenos_favoritos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cuenta_cliente_id',
        'negocio_id',
        'diseno_id',
    ];

    /**
     * @return BelongsTo<CuentaCliente, $this>
     */
    public function cuentaCliente(): BelongsTo
    {
        return $this->belongsTo(CuentaCliente::class);
    }

    /**
     * @return BelongsTo<Negocio, $this>
     */
    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }
}
