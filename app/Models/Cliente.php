<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'nombre',
        'celular',
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
}
