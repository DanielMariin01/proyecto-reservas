<?php

declare(strict_types=1);

namespace App\Http\Requests\Publico;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudGestionCitaPorToken extends FormRequest
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
            'accion' => ['required', Rule::in(['cancelar', 'reprogramar'])],
            'motivo' => ['nullable', 'string', 'max:500'],
            'inicio' => ['required_if:accion,reprogramar', 'date'],
        ];
    }
}
