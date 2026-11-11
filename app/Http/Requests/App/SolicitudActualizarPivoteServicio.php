<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudActualizarPivoteServicio extends FormRequest
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
            'precio' => ['nullable', 'integer', 'min:0'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
