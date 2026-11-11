<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\BloqueValidoPagina;
use Illuminate\Foundation\Http\FormRequest;

final class SolicitudActualizarBloquesPagina extends FormRequest
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
            'bloques' => ['present', 'array'],
            'bloques.*' => [new BloqueValidoPagina],
            'bloques.*.type' => ['required', 'string'],
            'bloques.*.data' => ['array'],
        ];
    }
}
