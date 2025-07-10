<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Se não houver user autenticado, só pode registrar se for o primeiro user
        if (!$user) {
            return User::count() === 0;
        }

        // Só admins podem registrar novos usuários
        return $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'tipo' => ['required', 'string', 'in:admin,enfermeiro,gestor,tecnico'],
            'telefone' => ['required', 'string', 'max:20']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais de 255 caracteres',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'Digite um e-mail válido',
            'email.unique' => 'Este e-mail já está em uso',
            'password.required' => 'A senha é obrigatória',
            'password.confirmed' => 'A confirmação de senha não corresponde',
            'tipo.required' => 'O tipo de usuário é obrigatório',
            'tipo.in' => 'Tipo de usuário inválido',
            'telefone.required' => 'O telefone é obrigatório',
            'telefone.max' => 'O telefone não pode ter mais de 20 caracteres'
        ];
    }
}
