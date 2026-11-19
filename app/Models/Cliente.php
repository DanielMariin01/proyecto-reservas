<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'clientes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'cuenta_cliente_id',
        'nombre',
        'celular',
        'documento',
        'fecha_nacimiento',
        'correo',
        'notas',
        'veces_no_asistio',
        'consentimiento_datos_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'consentimiento_datos_en' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Cita, $this>
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * @return HasMany<HistoriaClinica, $this>
     */
    public function historiasClinicas(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class);
    }

    /**
     * El "código de clienta" (RF-HCL-03) nunca se guarda: se calcula a
     * partir del id del cliente para no necesitar una columna ni lógica
     * de colisión.
     */
    public function codigoClienta(): string
    {
        return self::formatearCodigo($this->id);
    }

    public static function formatearCodigo(int $clienteId): string
    {
        return 'CL-'.str_pad((string) $clienteId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return HasMany<Resena, $this>
     */
    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class);
    }

    /**
     * @return HasMany<Pedido, $this>
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * @return BelongsTo<CuentaCliente, $this>
     */
    public function cuentaCliente(): BelongsTo
    {
        return $this->belongsTo(CuentaCliente::class);
    }
}
