<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get();

        return view('usuarios.index', [
            'usuarios' => $usuarios
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'admin' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'admin' => $request->admin ? true : false,
            'ativo' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso!',
            'usuario' => $usuario
        ]);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'admin' => 'boolean',
            'ativo' => 'boolean'
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->admin = $request->admin ? true : false;
        $usuario->ativo = $request->ativo ? true : false;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso!',
            'usuario' => $usuario
        ]);
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        // Verificar se é o último administrador
        if ($usuario->admin && User::where('admin', true)->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir o último administrador do sistema.'
            ], 422);
        }

        // Verificar se é o próprio usuário
        if ($usuario->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir o próprio usuário.'
            ], 422);
        }

        $usuario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso!'
        ]);
    }
}
