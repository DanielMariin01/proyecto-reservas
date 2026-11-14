<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\EstadoPedidoValor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudCambiarEstadoPedido extends FormRequest
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
            'estado' => ['required', Rule::in(array_column(EstadoPedidoValor::cases(), 'value'))],
        ];
    }
}
