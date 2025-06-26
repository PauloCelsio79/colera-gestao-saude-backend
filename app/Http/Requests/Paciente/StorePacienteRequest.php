<?php

namespace App\Http\Requests\Paciente;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:255'],
            'bi_numero' => ['required', 'string', 'max:255', 'unique:pacientes'],
            'telefone' => ['required', 'string', 'max:20'],
            'data_nascimento' => ['required', 'date', 'before:today'],
            'endereco' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'genero' => ['required', 'string', 'in:M,F']
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres',
            'bi_numero.required' => 'O número do BI é obrigatório',
            'bi_numero.unique' => 'Este número de BI já está cadastrado',
            'telefone.required' => 'O telefone é obrigatório',
            'telefone.max' => 'O telefone não pode ter mais de 20 caracteres',
            'data_nascimento.required' => 'A data de nascimento é obrigatória',
            'data_nascimento.date' => 'Data de nascimento inválida',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje',
            'endereco.required' => 'O endereço é obrigatório',
            'latitude.numeric' => 'A latitude deve ser um número',
            'latitude.between' => 'A latitude deve estar entre -90 e 90',
            'longitude.numeric' => 'A longitude deve ser um número',
            'longitude.between' => 'A longitude deve estar entre -180 e 180',
            'genero.required' => 'O gênero é obrigatório',
            'genero.in' => 'Gênero inválido'
        ];
    }
}
