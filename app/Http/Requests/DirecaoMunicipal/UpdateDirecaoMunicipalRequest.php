<?php

namespace App\Http\Requests\DirecaoMunicipal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDirecaoMunicipalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('direcao_municipal');

        return [
            'gabinete_provincial_id' => ['sometimes', 'required', 'exists:gabinete_provinciais,id'],
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'municipio' => ['sometimes', 'required', 'string', 'max:255', 'unique:direcao_municipais,municipio,' . $id],
            'endereco' => ['sometimes', 'required', 'string', 'max:255'],
            'telefone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['sometimes', 'required', 'email', 'unique:direcao_municipais,email,' . $id],
            'diretor' => ['sometimes', 'required', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'gabinete_provincial_id.required' => 'O gabinete provincial é obrigatório',
            'gabinete_provincial_id.exists' => 'Gabinete provincial não encontrado',
            'nome.required' => 'O nome é obrigatório',
            'municipio.required' => 'O município é obrigatório',
            'municipio.unique' => 'Este município já está cadastrado',
            'endereco.required' => 'O endereço é obrigatório',
            'telefone.required' => 'O telefone é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'E-mail inválido',
            'email.unique' => 'Este e-mail já está em uso',
            'diretor.required' => 'O nome do diretor é obrigatório'
        ];
    }
}
