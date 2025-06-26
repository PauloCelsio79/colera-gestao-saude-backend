<?php

namespace App\Http\Requests\Hospital;

use Illuminate\Foundation\Http\FormRequest;

class StoreHospitalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isGestor();
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
            'tipo' => ['required', 'string', 'in:geral,municipal,provincial,centro_medico,clinica,outro'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'leitos_totais' => ['required', 'integer', 'min:1'],
            'leitos_disponiveis' => ['required', 'integer', 'min:0', 'lte:leitos_totais'],
            'telefone' => ['required', 'string', 'max:20'],
            'endereco' => ['required', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do hospital é obrigatório',
            'tipo.required' => 'O tipo de hospital é obrigatório',
            'tipo.in' => 'Tipo de hospital inválido',
            'latitude.required' => 'A latitude é obrigatória',
            'latitude.between' => 'A latitude deve estar entre -90 e 90',
            'longitude.required' => 'A longitude é obrigatória',
            'longitude.between' => 'A longitude deve estar entre -180 e 180',
            'leitos_totais.required' => 'O número total de leitos é obrigatório',
            'leitos_totais.min' => 'O hospital deve ter pelo menos 1 leito',
            'leitos_disponiveis.required' => 'O número de leitos disponíveis é obrigatório',
            'leitos_disponiveis.min' => 'O número de leitos disponíveis não pode ser negativo',
            'leitos_disponiveis.lte' => 'O número de leitos disponíveis não pode ser maior que o total',
            'telefone.required' => 'O telefone é obrigatório',
            'endereco.required' => 'O endereço é obrigatório'
        ];
    }
}
