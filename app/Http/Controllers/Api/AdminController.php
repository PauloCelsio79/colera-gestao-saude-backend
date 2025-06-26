<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function permitirAcesso(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permitir' => 'required|boolean'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->ativo = $request->permitir;
        $user->save();

        return response()->json([
            'message' => $request->permitir ? 'Acesso permitido com sucesso' : 'Acesso negado com sucesso',
            'user' => $user
        ]);
    }
} 