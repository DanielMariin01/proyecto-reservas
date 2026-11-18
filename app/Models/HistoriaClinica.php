<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AutorizacionFotos;
use App\Enums\DecisionValoracion;
use App\Enums\FormaUna;
use App\Enums\LargoUna;
use App\Enums\ServicioDiseno;
use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\HistoriaClinicaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HistoriaClinica extends Model
{
    /** @use HasFactory<HistoriaClinicaFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'historias_clinicas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'cliente_id',
        'profesional_id',
        'usuario_id',
        'fecha',
        'alergias',
        'reaccion_anterior',
        'diabetes',
        'problemas_circulacion',
        'medicamentos',
        'heridas_actuales',
        'otra_condicion',
        'detalle_reaccion',
        'piel',
        'una',
        'hallazgos_visibles',
        'hallazgo_otro_texto',
        'observaciones_valoracion',
        'decision',
        'motivo_decision',
        'servicio_diseno',
        'servicio_diseno_otro_texto',
        'forma_una',
        'largo_una',
        'colores',
        'decoracion_referencia',
        'hora_inicio',
        'hora_final',
        'tecnica_realizada',
        'productos_marcas',
        'lote_vencimiento',
        'retiro_previo',
        'reparaciones',
        'reparaciones_detalle',
        'cambios_durante_servicio',
        'novedades_incidentes',
        'valor',
        'abono',
        'saldo',
        'revisio_resultado',
        'recibio_cuidados',
        'fecha_seguimiento',
        'proximo_mantenimiento_sugerido',
        'novedad_pendiente',
        'consentimiento_en',
        'autorizacion_fotos',
        'firma_clienta_path',
        'firma_profesional_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'alergias' => 'boolean',
            'reaccion_anterior' => 'boolean',
            'diabetes' => 'boolean',
            'problemas_circulacion' => 'boolean',
            'medicamentos' => 'boolean',
            'heridas_actuales' => 'boolean',
            'piel' => 'array',
            'una' => 'array',
            'hallazgos_visibles' => 'array',
            'decision' => DecisionValoracion::class,
            'servicio_diseno' => ServicioDiseno::class,
            'forma_una' => FormaUna::class,
            'largo_una' => LargoUna::class,
            'retiro_previo' => 'boolean',
            'reparaciones' => 'boolean',
            'revisio_resultado' => 'boolean',
            'recibio_cuidados' => 'boolean',
            'novedad_pendiente' => 'boolean',
            'fecha_seguimiento' => 'date',
            'proximo_mantenimiento_sugerido' => 'date',
            'consentimiento_en' => 'datetime',
            'autorizacion_fotos' => AutorizacionFotos::class,
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
     * @return BelongsTo<Profesional, $this>
     */
    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * El "código de clienta" (RF-HCL-03) nunca se guarda: se calcula a
     * partir del id del cliente para no necesitar una columna ni lógica
     * de colisión.
     */
    public function codigoClienta(): string
    {
        return 'CL-'.str_pad((string) $this->cliente_id, 6, '0', STR_PAD_LEFT);
    }
}
