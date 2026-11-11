<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudCrearCita extends FormRequest
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
            'profesional_id' => ['nullable', 'integer'],
            'servicio_id' => ['required', 'integer'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'nombre_cliente' => ['required', 'string', 'max:255'],
            'celular_cliente' => ['required', 'string', 'max:20'],
            'correo_cliente' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string'],
            'forzar' => ['nullable', 'boolean'],
        ];
    }
}
