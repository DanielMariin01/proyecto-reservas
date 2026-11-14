<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudProducto extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'foto' => ['nullable', 'string'],
            'precio' => ['required', 'integer', 'min:0'],
            'visible' => ['boolean'],
        ];
    }
}
