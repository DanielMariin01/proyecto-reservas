<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudBloqueoRapido extends FormRequest
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
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
