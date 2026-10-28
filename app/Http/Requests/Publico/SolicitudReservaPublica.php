<?php

declare(strict_types=1);

namespace App\Http\Requests\Publico;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudReservaPublica extends FormRequest
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
            'servicio_id' => ['required', 'integer'],
            'profesional_id' => ['nullable', 'integer'],
            'inicio' => ['required', 'date'],
            'nombre' => ['required', 'string', 'max:255'],
            'celular' => ['required', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:255'],
            'nota' => ['nullable', 'string', 'max:1000'],
            // RNF-05 (Ley 1581 de 2012): la casilla de autorización de datos es obligatoria.
            'autorizacion_datos' => ['required', 'accepted'],
            // Honeypot: un campo que ningún visitante real llena.
            'sitio_web' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'autorizacion_datos.accepted' => 'Debes autorizar el tratamiento de tus datos personales.',
        ];
    }
}
