<?php

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'El título debe ser un texto válido.',
            'title.max' => 'El título no puede superar los 255 caracteres.',

            'description.string' => 'La descripción debe ser un texto válido.',

            'end_date.date' => 'La fecha de fin no es válida.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
