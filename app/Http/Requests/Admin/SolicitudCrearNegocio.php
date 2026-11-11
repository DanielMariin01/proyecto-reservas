<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TipoNegocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudCrearNegocio extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('negocios', 'slug')],
            'tipo' => ['required', Rule::in(array_column(TipoNegocio::cases(), 'value'))],
            'zona_horaria' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'paleta_marca_id' => ['nullable', 'integer', Rule::exists('paletas_marca', 'id')],
            'permite_editar_pagina' => ['boolean'],
            'nombre_dueno' => ['required', 'string', 'max:255'],
            'email_dueno' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')],
        ];
    }
}
