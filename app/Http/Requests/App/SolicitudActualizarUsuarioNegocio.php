<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\RolNegocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudActualizarUsuarioNegocio extends FormRequest
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
            'telefono' => ['nullable', 'string', 'max:255'],
            'rol' => ['required', Rule::in(array_column(RolNegocio::cases(), 'value'))],
        ];
    }
}
