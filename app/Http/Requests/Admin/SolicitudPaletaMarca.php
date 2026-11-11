<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\ColorConContrasteAaConBlanco;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SolicitudPaletaMarca extends FormRequest
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
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('paletas_marca', 'nombre')->ignore($this->route('paleta')),
            ],
            'color_primario' => ['required', 'string', new ColorConContrasteAaConBlanco],
            'color_secundario' => ['required', 'string'],
        ];
    }
}
