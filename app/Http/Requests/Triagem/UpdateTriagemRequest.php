<?php

namespace App\Http\Requests\Triagem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTriagemRequest extends FormRequest
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
            'sintomas' => ['sometimes', 'required', 'array'],
            'sintomas.diarreia' => ['sometimes', 'required', 'boolean'],
            'sintomas.vomito' => ['sometimes', 'required', 'boolean'],
            'sintomas.desidratacao' => ['sometimes', 'required', 'boolean'],
            'sintomas.dor_abdominal' => ['sometimes', 'required', 'boolean'],
            'sintomas.fraqueza' => ['sometimes', 'required', 'boolean'],
            'nivel_risco' => ['sometimes', 'required', 'string', 'in:baixo,medio,alto'],
            'observacoes' => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'sintomas.required' => 'Os sintomas são obrigatórios',
            'sintomas.array' => 'Os sintomas devem ser um array',
            'sintomas.*.required' => 'Todos os sintomas devem ser informados',
            'sintomas.*.boolean' => 'Os sintomas devem ser verdadeiro ou falso',
            'nivel_risco.required' => 'O nível de risco é obrigatório',
            'nivel_risco.in' => 'Nível de risco inválido'
        ];
    }
}
