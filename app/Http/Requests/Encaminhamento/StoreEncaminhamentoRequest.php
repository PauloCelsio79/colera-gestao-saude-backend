<?php

namespace App\Http\Requests\Encaminhamento;

use Illuminate\Foundation\Http\FormRequest;

class StoreEncaminhamentoRequest extends FormRequest
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
            'triagem_id' => ['required', 'exists:triagens,id'],
            'hospital_id' => ['required', 'exists:hospitais,id'],
            'status' => ['required', 'string', 'in:pendente,concluido,cancelado'],
            'motivo_cancelamento' => ['nullable', 'string', 'required_if:status,cancelado'],
            'data_chegada' => ['nullable', 'date', 'required_if:status,concluido']
        ];
    }

    public function messages(): array
    {
        return [
            'triagem_id.required' => 'O ID da triagem é obrigatório',
            'triagem_id.exists' => 'Triagem não encontrada',
            'hospital_id.required' => 'O ID do hospital é obrigatório',
            'hospital_id.exists' => 'Hospital não encontrado',
            'status.required' => 'O status é obrigatório',
            'status.in' => 'Status inválido',
            'motivo_cancelamento.required_if' => 'O motivo do cancelamento é obrigatório quando o status é cancelado',
            'data_chegada.required_if' => 'A data de chegada é obrigatória quando o status é concluído',
            'data_chegada.date' => 'A data de chegada deve ser uma data válida'
        ];
    }
}
