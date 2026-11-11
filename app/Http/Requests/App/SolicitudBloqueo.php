<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\TipoBloqueo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudBloqueo extends FormRequest
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
            'tipo' => ['required', Rule::in(array_column(TipoBloqueo::cases(), 'value'))],
            'fecha_inicio' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'fecha_fin' => ['required', 'date'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
