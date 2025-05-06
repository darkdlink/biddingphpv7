<?php

namespace App\Http\Controllers;

use App\Models\Proposta;
use App\Models\Licitacao;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PropostaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $propostas = Proposta::with(['licitacao', 'cliente']);

            // Filtros
            if ($request->has('cliente_id') && $request->cliente_id) {
                $propostas->where('cliente_id', $request->cliente_id);
            }

            if ($request->has('status') && $request->status) {
                $propostas->where('status', $request->status);
            }

            if ($request->has('data_min') && $request->data_min) {
                $propostas->whereDate('data_envio', '>=', $request->data_min);
            }

            if ($request->has('data_max') && $request->data_max) {
                $propostas->whereDate('data_envio', '<=', $request->data_max);
            }

            $propostas = $propostas->orderBy('created_at', 'desc')->paginate(15);
            $clientes = Cliente::all();

            return view('propostas.index', [
                'propostas' => $propostas,
                'clientes' => $clientes,
                'filtros' => $request->all()
            ]);
        } catch (\Exception $e) {
            return view('propostas.index', [
                'propostas' => collect(),
                'clientes' => collect(),
                'filtros' => $request->all(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        $licitacoes = Licitacao::where('data_encerramento_proposta', '>=', Carbon::now())
                                ->orderBy('data_encerramento_proposta', 'asc')
                                ->get();
        $clientes = Cliente::all();

        return view('propostas.create', [
            'licitacoes' => $licitacoes,
            'clientes' => $clientes
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licitacao_id' => 'required|exists:licitacoes,id',
            'cliente_id' => 'required|exists:clientes,id',
            'valor_proposta' => 'required|numeric|min:0',
            'descricao' => 'required|string'
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

        // Criar proposta
        $proposta = new Proposta();
        $proposta->licitacao_id = $request->licitacao_id;
        $proposta->cliente_id = $request->cliente_id;
        $proposta->valor_proposta = $request->valor_proposta;
        $proposta->descricao = $request->descricao;
        $proposta->status = 'elaboracao';
        $proposta->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proposta criada com sucesso!',
                'proposta' => $proposta
            ]);
        }

        return redirect()->route('propostas.show', $proposta->id)
                         ->with('success', 'Proposta criada com sucesso!');
    }

    public function show($id)
    {
        $proposta = Proposta::with(['licitacao', 'cliente'])->findOrFail($id);

        return view('propostas.show', [
            'proposta' => $proposta
        ]);
    }

    public function edit($id)
    {
        $proposta = Proposta::with(['licitacao', 'cliente'])->findOrFail($id);
        $clientes = Cliente::all();

        return view('propostas.edit', [
            'proposta' => $proposta,
            'clientes' => $clientes
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'valor_proposta' => 'required|numeric|min:0',
            'descricao' => 'required|string',
            'status' => 'required|in:elaboracao,enviada,aceita,rejeitada,vencedora'
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

        $proposta = Proposta::findOrFail($id);
        $proposta->cliente_id = $request->cliente_id;
        $proposta->valor_proposta = $request->valor_proposta;
        $proposta->descricao = $request->descricao;
        $proposta->status = $request->status;

        // Se status mudou para enviada, atualiza a data de envio
        if ($proposta->status != 'elaboracao' && !$proposta->data_envio) {
            $proposta->data_envio = Carbon::now();
        }

        $proposta->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proposta atualizada com sucesso!',
                'proposta' => $proposta
            ]);
        }

        return redirect()->route('propostas.show', $proposta->id)
                         ->with('success', 'Proposta atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $proposta = Proposta::findOrFail($id);

        // Verifica se a proposta já foi enviada
        if ($proposta->status != 'elaboracao') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir uma proposta que já foi enviada!'
            ], 422);
        }

        $proposta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proposta excluída com sucesso!'
        ]);
    }

    public function enviar($id)
    {
        $proposta = Proposta::findOrFail($id);

        // Verifica se a proposta já foi enviada
        if ($proposta->status != 'elaboracao') {
            return response()->json([
                'success' => false,
                'message' => 'Proposta já foi enviada anteriormente!'
            ], 422);
        }

    // Verifica se a data de encerramento da licitação já passou
    $licitacao = $proposta->licitacao;
    if (Carbon::now() > $licitacao->data_encerramento_proposta) {
        return response()->json([
            'success' => false,
            'message' => 'Não é possível enviar a proposta, pois o prazo de encerramento já passou!'
        ], 422);
    }

    // Atualiza o status da proposta
    $proposta->status = 'enviada';
    $proposta->data_envio = Carbon::now();
    $proposta->save();

    return response()->json([
        'success' => true,
        'message' => 'Proposta enviada com sucesso!',
        'proposta' => $proposta
    ]);
    }

    public function atualizarStatus(Request $request, $id)
    {
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:elaboracao,enviada,aceita,rejeitada,vencedora'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $proposta = Proposta::findOrFail($id);

    // Se a proposta está em elaboração, não pode mudar para aceita, rejeitada ou vencedora
    if ($proposta->status == 'elaboracao' && in_array($request->status, ['aceita', 'rejeitada', 'vencedora'])) {
        return response()->json([
            'success' => false,
            'message' => 'Não é possível mudar o status para ' . $request->status . ' de uma proposta em elaboração!'
        ], 422);
    }

    $proposta->status = $request->status;

    // Se status mudou para enviada e não tinha data de envio, atualiza a data
    if ($proposta->status == 'enviada' && !$proposta->data_envio) {
        $proposta->data_envio = Carbon::now();
    }

    // Se status mudou para vencedora, marca as outras propostas como rejeitadas
    if ($proposta->status == 'vencedora') {
        Proposta::where('licitacao_id', $proposta->licitacao_id)
            ->where('id', '!=', $proposta->id)
            ->update(['status' => 'rejeitada']);
    }

    $proposta->save();

    return response()->json([
        'success' => true,
        'message' => 'Status da proposta atualizado com sucesso!',
        'proposta' => $proposta
    ]);
    }
}
