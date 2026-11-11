<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\EstadoSuscripcion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudCambiarEstadoNegocio extends FormRequest
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
            'estado_destino' => ['required', Rule::in(array_column(EstadoSuscripcion::cases(), 'value'))],
            'motivo' => ['required', 'string', 'max:1000'],
        ];
    }
}
