<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'categoria_producto_id' => [
                'nullable',
                'integer',
                Rule::exists('categorias_producto', 'id')->where('negocio_id', Negocio::actual()->id),
            ],
            'descripcion' => ['nullable', 'string'],
            'foto' => ['nullable', 'string'],
            'precio' => ['required', 'integer', 'min:0'],
            'visible' => ['boolean'],
        ];
    }
}
