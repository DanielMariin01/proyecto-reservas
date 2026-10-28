<?php

declare(strict_types=1);

namespace App\Http\Requests\Publico;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;

final class SolicitudDisponibilidadPublica extends FormRequest
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
            'servicio' => ['required', 'integer'],
            'profesional' => ['nullable', 'integer'],
            'mes' => ['nullable', 'date_format:Y-m'],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator): void {
            if ($this->filled('mes') === $this->filled('fecha')) {
                $validator->errors()->add('mes', 'Envía "mes" o "fecha", pero no los dos ni ninguno.');
            }
        });
    }

    public function consultaPorMes(): bool
    {
        return $this->filled('mes');
    }
}
