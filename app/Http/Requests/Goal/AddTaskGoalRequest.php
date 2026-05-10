<?php

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class AddTaskGoalRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'xp_per_day' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título de la tarea es obligatorio.',
            'title.string' => 'El título debe ser un texto válido.',
            'title.max' => 'El título no puede superar los 255 caracteres.',

            'xp_per_day.required' => 'El XP por día es obligatorio.',
            'xp_per_day.integer' => 'El XP por día debe ser un número entero.',
        ];
    }
}
