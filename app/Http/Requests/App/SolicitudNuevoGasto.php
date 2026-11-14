<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudNuevoGasto extends FormRequest
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
            'monto' => ['required', 'integer', 'min:1'],
            'descripcion' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
        ];
    }
}
