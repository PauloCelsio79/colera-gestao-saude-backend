<?php

namespace App\Http\Requests\GabineteProvincial;

use Illuminate\Foundation\Http\FormRequest;

class StoreGabineteProvincialRequest extends FormRequest
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
        return [
            'nome' => ['required', 'string', 'max:255'],
            'provincia' => ['required', 'string', 'max:255', 'unique:gabinete_provinciais,provincia'],
            'endereco' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:gabinete_provinciais,email'],
            'diretor' => ['required', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório',
            'provincia.required' => 'A província é obrigatória',
            'provincia.unique' => 'Esta província já está cadastrada',
            'endereco.required' => 'O endereço é obrigatório',
            'telefone.required' => 'O telefone é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'E-mail inválido',
            'email.unique' => 'Este e-mail já está em uso',
            'diretor.required' => 'O nome do diretor é obrigatório'
        ];
    }
}
