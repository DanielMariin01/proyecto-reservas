<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudHorarioSemanal extends FormRequest
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
            'dias' => ['required', 'array'],
            'dias.*.disponible' => ['required', 'boolean'],
            'dias.*.rangos' => ['array'],
            'dias.*.rangos.*.hora_inicio' => ['required', 'date_format:H:i'],
            'dias.*.rangos.*.hora_fin' => ['required', 'date_format:H:i', 'after:dias.*.rangos.*.hora_inicio'],
        ];
    }
}
