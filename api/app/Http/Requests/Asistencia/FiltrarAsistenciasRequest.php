<?php

namespace App\Http\Requests\Asistencia;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FiltrarAsistenciasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ];
    }

    public function fecha(): string
    {
        return $this->input('fecha') ?? now()->toDateString();
    }
}
