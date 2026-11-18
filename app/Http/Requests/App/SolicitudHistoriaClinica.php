<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\AutorizacionFotos;
use App\Enums\CondicionPiel;
use App\Enums\CondicionUna;
use App\Enums\DecisionValoracion;
use App\Enums\FormaUna;
use App\Enums\HallazgoVisible;
use App\Enums\LargoUna;
use App\Enums\ServicioDiseno;
use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudHistoriaClinica extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'profesional_id' => ['required', 'integer', Rule::exists('profesionales', 'id')->where('negocio_id', Negocio::actual()->id)],
            'fecha' => ['required', 'date'],

            'alergias' => ['boolean'],
            'reaccion_anterior' => ['boolean'],
            'diabetes' => ['boolean'],
            'problemas_circulacion' => ['boolean'],
            'medicamentos' => ['boolean'],
            'heridas_actuales' => ['boolean'],
            'otra_condicion' => ['nullable', 'string'],
            'detalle_reaccion' => ['nullable', 'string'],

            'piel' => ['required', 'array', 'min:1'],
            'piel.*' => [Rule::in(array_column(CondicionPiel::cases(), 'value'))],
            'una' => ['required', 'array', 'min:1'],
            'una.*' => [Rule::in(array_column(CondicionUna::cases(), 'value'))],
            'hallazgos_visibles' => ['nullable', 'array'],
            'hallazgos_visibles.*' => [Rule::in(array_column(HallazgoVisible::cases(), 'value'))],
            'hallazgo_otro_texto' => ['nullable', 'string'],
            'observaciones_valoracion' => ['nullable', 'string'],
            'decision' => ['required', Rule::in(array_column(DecisionValoracion::cases(), 'value'))],
            'motivo_decision' => ['nullable', 'string'],

            'servicio_diseno' => ['required', Rule::in(array_column(ServicioDiseno::cases(), 'value'))],
            'servicio_diseno_otro_texto' => ['nullable', 'string'],
            'forma_una' => ['required', Rule::in(array_column(FormaUna::cases(), 'value'))],
            'largo_una' => ['required', Rule::in(array_column(LargoUna::cases(), 'value'))],
            'colores' => ['nullable', 'string'],
            'decoracion_referencia' => ['nullable', 'string'],

            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_final' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tecnica_realizada' => ['nullable', 'string'],
            'productos_marcas' => ['nullable', 'string'],
            'lote_vencimiento' => ['nullable', 'string'],
            'retiro_previo' => ['boolean'],
            'reparaciones' => ['boolean'],
            'reparaciones_detalle' => ['nullable', 'string'],
            'cambios_durante_servicio' => ['nullable', 'string'],
            'novedades_incidentes' => ['nullable', 'string'],

            'valor' => ['required', 'integer', 'min:0'],
            'abono' => ['required', 'integer', 'min:0'],
            'saldo' => ['required', 'integer', 'min:0'],
            'revisio_resultado' => ['boolean'],
            'recibio_cuidados' => ['boolean'],
            'fecha_seguimiento' => ['nullable', 'date'],
            'proximo_mantenimiento_sugerido' => ['nullable', 'date'],
            'novedad_pendiente' => ['boolean'],

            'consentimiento_aceptado' => ['boolean'],
            'autorizacion_fotos' => ['required', Rule::in(array_column(AutorizacionFotos::cases(), 'value'))],
            'firma_clienta_base64' => ['nullable', 'string', 'regex:/^data:image\/(png|jpeg);base64,/'],
            'firma_profesional_base64' => ['nullable', 'string', 'regex:/^data:image\/(png|jpeg);base64,/'],
        ];
    }
}
