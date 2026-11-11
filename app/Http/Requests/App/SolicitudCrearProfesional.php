<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudCrearProfesional extends FormRequest
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
            'especialidad' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'string'],
            'biografia' => ['nullable', 'string'],
            'certificaciones' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
            'nombre_usuario' => ['nullable', 'string', 'max:255', 'required_with:email_usuario'],
            'email_usuario' => ['nullable', 'email', 'max:255', 'required_with:nombre_usuario', Rule::unique('usuarios', 'email')],
        ];
    }
}
