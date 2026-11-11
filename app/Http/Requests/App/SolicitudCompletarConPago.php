<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudCompletarConPago extends FormRequest
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
            'metodo' => ['required', Rule::in(array_column(MetodoPago::cases(), 'value'))],
            'valor' => ['required', 'integer', 'min:0'],
            'propina' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
