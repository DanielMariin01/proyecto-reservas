<?php

declare(strict_types=1);

namespace App\Http\Requests\Publico;

use App\Enums\TipoEntregaValor;
use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudPedidoPublico extends FormRequest
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
        /** @var Negocio $negocio */
        $negocio = $this->route('negocio');

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')->where('negocio_id', $negocio->id)->where('visible', true),
            ],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:255'],
            'celular' => ['required', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:255'],
            'nota' => ['nullable', 'string', 'max:1000'],
            'tipo_entrega' => ['required', Rule::in(array_column(TipoEntregaValor::cases(), 'value'))],
            'barrio' => ['required_if:tipo_entrega,domicilio', 'nullable', 'string', 'max:255'],
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
            'barrio.required_if' => 'El barrio es obligatorio para domicilio.',
        ];
    }
}
