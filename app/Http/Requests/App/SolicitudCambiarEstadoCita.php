<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\EstadoCitaValor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudCambiarEstadoCita extends FormRequest
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
            'estado' => ['required', Rule::in(array_column(EstadoCitaValor::cases(), 'value'))],
        ];
    }
}
