<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\RolNegocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudInvitarUsuario extends FormRequest
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
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')],
            'rol' => ['required', Rule::in(array_column(RolNegocio::cases(), 'value'))],
        ];
    }
}
