<?php

namespace App\Http\Requests\Triagem;

use Illuminate\Foundation\Http\FormRequest;

class StoreTriagemRequest extends FormRequest
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
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'sintomas' => ['required', 'array'],
            'sintomas.diarreia' => ['required', 'boolean'],
            'sintomas.vomito' => ['required', 'boolean'],
            'sintomas.desidratacao' => ['required', 'boolean'],
            'sintomas.dor_abdominal' => ['required', 'boolean'],
            'sintomas.fraqueza' => ['required', 'boolean'],
            'nivel_risco' => ['required', 'string', 'in:baixo,medio,alto'],
            'observacoes' => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required' => 'O ID do paciente é obrigatório',
            'paciente_id.exists' => 'Paciente não encontrado',
            'sintomas.required' => 'Os sintomas são obrigatórios',
            'sintomas.array' => 'Os sintomas devem ser um array',
            'sintomas.*.required' => 'Todos os sintomas devem ser informados',
            'sintomas.*.boolean' => 'Os sintomas devem ser verdadeiro ou falso',
            'nivel_risco.required' => 'O nível de risco é obrigatório',
            'nivel_risco.in' => 'Nível de risco inválido'
        ];
    }
}
