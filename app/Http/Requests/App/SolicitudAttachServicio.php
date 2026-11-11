<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudAttachServicio extends FormRequest
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
            'servicio_id' => [
                'required',
                'integer',
                Rule::exists('servicios', 'id')->where('negocio_id', Negocio::actual()->id),
            ],
            'precio' => ['nullable', 'integer', 'min:0'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
