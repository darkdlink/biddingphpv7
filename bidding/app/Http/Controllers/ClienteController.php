<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ClienteController extends Controller
{
    /**
     * Exibe a listagem de clientes
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clientes = Cliente::orderBy('nome', 'asc')->paginate(15);
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Exibe o formulário para criar um novo cliente
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Armazena um novo cliente no banco de dados
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:clientes',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'uf' => 'required|string|size:2',
            'cep' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('clientes.create')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $cliente = new Cliente();
            $cliente->nome = $request->nome;
            $cliente->cnpj = $request->cnpj;
            $cliente->email = $request->email;
            $cliente->telefone = $request->telefone;
            $cliente->endereco = $request->endereco;
            $cliente->cidade = $request->cidade;
            $cliente->uf = $request->uf;
            $cliente->cep = $request->cep;
            $cliente->observacoes = $request->observacoes;
            $cliente->save();

            return redirect()
                ->route('clientes.index')
                ->with('success', 'Cliente cadastrado com sucesso!');
        } catch (Exception $e) {
            return redirect()
                ->route('clientes.create')
                ->with('error', 'Erro ao cadastrar cliente: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Exibe os detalhes de um cliente específico
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = Cliente::with('propostas')->findOrFail($id);
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Exibe o formulário para editar um cliente
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Atualiza um cliente no banco de dados
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:clientes,cnpj,' . $id,
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'uf' => 'required|string|size:2',
            'cep' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('clientes.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $cliente->nome = $request->nome;
            $cliente->cnpj = $request->cnpj;
            $cliente->email = $request->email;
            $cliente->telefone = $request->telefone;
            $cliente->endereco = $request->endereco;
            $cliente->cidade = $request->cidade;
            $cliente->uf = $request->uf;
            $cliente->cep = $request->cep;
            $cliente->observacoes = $request->observacoes;
            $cliente->save();

            return redirect()
                ->route('clientes.show', $id)
                ->with('success', 'Cliente atualizado com sucesso!');
        } catch (Exception $e) {
            return redirect()
                ->route('clientes.edit', $id)
                ->with('error', 'Erro ao atualizar cliente: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove um cliente do banco de dados
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            // Verificar se existem propostas vinculadas a este cliente
            if ($cliente->propostas()->count() > 0) {
                return redirect()
                    ->route('clientes.index')
                    ->with('error', 'Não é possível excluir este cliente pois existem propostas vinculadas a ele.');
            }

            $cliente->delete();

            return redirect()
                ->route('clientes.index')
                ->with('success', 'Cliente excluído com sucesso!');
        } catch (Exception $e) {
            return redirect()
                ->route('clientes.index')
                ->with('error', 'Erro ao excluir cliente: ' . $e->getMessage());
        }
    }

    /**
     * Busca clientes com base em um termo de pesquisa
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $termo = $request->input('termo');

        $clientes = Cliente::where('nome', 'like', "%{$termo}%")
            ->orWhere('cnpj', 'like', "%{$termo}%")
            ->orWhere('email', 'like', "%{$termo}%")
            ->orderBy('nome', 'asc')
            ->paginate(15);

        return view('clientes.index', compact('clientes', 'termo'));
    }

    /**
     * Retorna clientes em formato JSON para uso em requisições AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getClientesJson(Request $request)
    {
        $termo = $request->input('termo', '');

        $clientes = Cliente::where('nome', 'like', "%{$termo}%")
            ->orWhere('cnpj', 'like', "%{$termo}%")
            ->orderBy('nome', 'asc')
            ->take(10)
            ->get(['id', 'nome', 'cnpj']);

        return response()->json($clientes);
    }
}
