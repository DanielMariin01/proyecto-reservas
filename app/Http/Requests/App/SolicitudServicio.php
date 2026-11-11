<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudServicio extends FormRequest
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
            'categoria_servicio_id' => [
                'nullable',
                'integer',
                Rule::exists('categorias_servicio', 'id')->where('negocio_id', Negocio::actual()->id),
            ],
            'descripcion' => ['nullable', 'string'],
            'foto' => ['nullable', 'string'],
            'duracion_minutos' => ['required', 'integer', 'min:'.Servicio::DURACION_MINIMA_MINUTOS],
            'precio' => ['required', 'integer', 'min:0'],
            'margen_antes_minutos' => ['nullable', 'integer', 'min:0'],
            'margen_despues_minutos' => ['nullable', 'integer', 'min:0'],
            'intervalo_franjas_minutos' => ['nullable', 'integer', 'min:1'],
            'visible' => ['boolean'],
        ];
    }
}
