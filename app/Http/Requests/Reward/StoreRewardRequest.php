<?php

namespace App\Http\Requests\Reward;

use Illuminate\Foundation\Http\FormRequest;

class StoreRewardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'points_cost' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la recompensa es obligatorio.',
            'name.string' => 'El nombre debe ser un texto válido.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',

            'points_cost.required' => 'El costo en puntos es obligatorio.',
            'points_cost.integer' => 'El costo en puntos debe ser un número entero.',
            'points_cost.min' => 'El costo en puntos debe ser al menos 1.',
        ];
    }
}
