<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Notifications\UsuarioLogou;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // Se não houver usuários, força o tipo como admin
        if (User::count() === 0) {
            $data['tipo'] = 'admin';
        }
        // Se não for o primeiro usuário, verifica se quem está criando é admin
        elseif (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'Apenas administradores podem registrar novos usuários'
            ], 403);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tipo' => $data['tipo'],
            'telefone' => $data['telefone'],
            'ativo' => true
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.']
            ]);
        }

        if (!$user->ativo) {
            throw ValidationException::withMessages([
                'email' => ['Esta conta está desativada. Entre em contato com o administrador.']
            ]);
        }

        // Enviar notificação para todos os admins
        $admins = \App\Models\User::where('tipo', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new UsuarioLogou($user));
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
}
