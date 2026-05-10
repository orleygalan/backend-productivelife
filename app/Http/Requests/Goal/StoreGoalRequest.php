<?php

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'tasks' => ['required', 'array', 'min:2'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.xp_per_day' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título de la meta es obligatorio.',
            'title.string' => 'El título debe ser un texto válido.',
            'title.max' => 'El título no puede superar los 255 caracteres.',

            'description.string' => 'La descripción debe ser un texto válido.',

            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date' => 'La fecha de inicio no es válida.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o una fecha futura.',

            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date' => 'La fecha de fin no es válida.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',

            'tasks.required' => 'Debes agregar al menos 2 tareas a la meta.',
            'tasks.array' => 'Las tareas deben enviarse en formato de lista.',
            'tasks.min' => 'La meta debe tener al menos 2 tareas diarias.',

            'tasks.*.title.required' => 'Cada tarea debe tener un título.',
            'tasks.*.title.string' => 'El título de cada tarea debe ser un texto válido.',
            'tasks.*.title.max' => 'El título de cada tarea no puede superar los 255 caracteres.',

            'tasks.*.xp_per_day.required' => 'Cada tarea debe tener un valor de XP por día.',
            'tasks.*.xp_per_day.integer' => 'El XP por día debe ser un número entero.',
        ];
    }
}
