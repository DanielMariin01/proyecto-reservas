<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudActualizarContenidoPagina extends FormRequest
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
            'bloques' => ['present', 'array'],
            'bloques.*.indice' => ['required', 'integer', 'min:0'],
            'bloques.*.data' => ['required', 'array'],
        ];
    }
}
