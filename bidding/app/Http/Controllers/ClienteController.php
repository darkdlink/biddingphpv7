<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::paginate(15);

        return view('clientes.index', [
            'clientes' => $clientes
        ]);
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|size:14|unique:clientes',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'uf' => 'required|string|size:2',
            'cep' => 'required|string|size:8'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $cliente = Cliente::create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente cadastrado com sucesso!',
                'cliente' => $cliente
            ]);
        }

        return redirect()->route('clientes.show', $cliente->id)
                         ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.show', [
            'cliente' => $cliente
        ]);
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.edit', [
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|size:14|unique:clientes,cnpj,' . $cliente->id,
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'uf' => 'required|string|size:2',
            'cep' => 'required|string|size:8'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $cliente->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente atualizado com sucesso!',
                'cliente' => $cliente
            ]);
        }

        return redirect()->route('clientes.show', $cliente->id)
                         ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        // Verificar se o cliente tem propostas
        if ($cliente->propostas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir o cliente, pois existem propostas vinculadas a ele!'
            ], 422);
        }

        $cliente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente excluído com sucesso!'
        ]);
    }

    public function lista()
    {
        $clientes = Cliente::select('id', 'nome', 'cnpj')->get();

        return response()->json($clientes);
    }
}
