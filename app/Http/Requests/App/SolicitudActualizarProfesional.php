<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitudActualizarProfesional extends FormRequest
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
            'activo' => ['boolean'],
        ];
    }
}
