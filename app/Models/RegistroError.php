<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RNF-OBS-03: una fila por cada excepción que pasa por el report() de
 * bootstrap/app.php (ver App\Actions\RegistrarErrorSistema), para que el
 * panel de salud del super admin tenga algo que listar y filtrar por
 * negocio. El código de referencia que ve el usuario (RNF-OBS-07) sigue
 * derivándose del id_peticion (App\Actions\GenerarCodigoDeReferencia);
 * esta tabla es solo un índice de consulta, no la fuente de ese código.
 */
final class RegistroError extends Model
{
    protected $table = 'registros_error';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'usuario_id',
        'id_peticion',
        'codigo_referencia',
        'clase_excepcion',
        'mensaje',
        'ruta',
        'metodo_http',
        'estado_http',
    ];

    /**
     * @return BelongsTo<Negocio, $this>
     */
    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
