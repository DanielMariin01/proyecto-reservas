<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RegistroAuditoria extends Model
{
    protected $table = 'registros_auditoria';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'usuario_id',
        'accion',
        'modelo',
        'modelo_id',
        'valores_antes',
        'valores_despues',
        'motivo',
        'hecho_por_soporte',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valores_antes' => 'array',
            'valores_despues' => 'array',
            'hecho_por_soporte' => 'boolean',
        ];
    }

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
