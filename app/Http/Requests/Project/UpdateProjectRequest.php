<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:3', 'max:100'],
            'description' => ['nullable', 'string', 'max:700'],
            'status' => ['sometimes', 'in:active,completed,archived']
        ];
    }

    public function messages()
    {
        return [
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede superar 100 caracteres.',
            'description.max' => 'La descripción no puede superar 500 caracteres.',
            'status.in' => 'El estado debe ser active, completed o archived.',
        ];
    }
}
