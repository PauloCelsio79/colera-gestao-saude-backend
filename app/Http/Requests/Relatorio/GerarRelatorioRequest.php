<?php

namespace App\Http\Requests\Relatorio;

use Illuminate\Foundation\Http\FormRequest;

class GerarRelatorioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas gestores e administradores podem gerar relatórios
        return auth()->user()->isGestor() || auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:casos_por_regiao,evolucao_temporal,distribuicao_risco,ocupacao_hospitais,eficiencia_encaminhamentos'],
            'periodo_inicio' => ['required', 'date'],
            'periodo_fim' => ['required', 'date', 'after_or_equal:periodo_inicio'],
            'formato' => ['required', 'string', 'in:json,pdf,excel'],
            'filtros' => ['nullable', 'array'],
            'filtros.hospital_id' => ['nullable', 'exists:hospitais,id'],
            'filtros.nivel_risco' => ['nullable', 'in:baixo,medio,alto'],
            'filtros.status_encaminhamento' => ['nullable', 'in:pendente,concluido,cancelado']
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo de relatório é obrigatório',
            'tipo.in' => 'Tipo de relatório inválido',
            'periodo_inicio.required' => 'A data inicial é obrigatória',
            'periodo_inicio.date' => 'Data inicial inválida',
            'periodo_fim.required' => 'A data final é obrigatória',
            'periodo_fim.date' => 'Data final inválida',
            'periodo_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial',
            'formato.required' => 'O formato do relatório é obrigatório',
            'formato.in' => 'Formato de relatório inválido',
            'filtros.hospital_id.exists' => 'Hospital não encontrado',
            'filtros.nivel_risco.in' => 'Nível de risco inválido',
            'filtros.status_encaminhamento.in' => 'Status de encaminhamento inválido'
        ];
    }
}
