<?php

namespace App\Http\Controllers;

use App\Models\Licitacao;
use App\Services\PncpApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicitacaoController extends Controller
{
    protected $pncpApiService;

    public function __construct(PncpApiService $pncpApiService)
    {
        $this->pncpApiService = $pncpApiService;
    }

    public function index(Request $request)
    {
        $licitacoes = Licitacao::query();

        // Aplicar filtros
        if ($request->has('uf') && !empty($request->uf)) {
            $licitacoes->where('uf', $request->uf);
        }

        if ($request->has('modalidade') && !empty($request->modalidade)) {
            $licitacoes->where('modalidade_nome', 'like', '%' . $request->modalidade . '%');
        }

        if ($request->has('data_min') && !empty($request->data_min)) {
            $licitacoes->where('data_encerramento_proposta', '>=', $request->data_min);
        }

        if ($request->has('data_max') && !empty($request->data_max)) {
            $licitacoes->where('data_encerramento_proposta', '<=', $request->data_max);
        }

        if ($request->has('valor_min') && !empty($request->valor_min)) {
            $licitacoes->where('valor_total_estimado', '>=', $request->valor_min);
        }

        if ($request->has('valor_max') && !empty($request->valor_max)) {
            $licitacoes->where('valor_total_estimado', '<=', $request->valor_max);
        }

        if ($request->has('interesse')) {
            $licitacoes->where('interesse', $request->interesse == '1');
        }

        // Ordenação
        $sortField = $request->get('sort', 'data_encerramento_proposta');
        $sortDirection = $request->get('direction', 'asc');

        $licitacoes = $licitacoes->orderBy($sortField, $sortDirection);

        // Paginação
        $licitacoes = $licitacoes->paginate(15);

        // Retornar a view com as licitações
        return view('licitacoes.index', [
            'licitacoes' => $licitacoes,
            'filtros' => $request->all()
        ]);
    }

    public function sincronizar(Request $request)
    {
        try {
            // Data padrão: próximos 3 meses
            $dataFinal = Carbon::now()->addMonths(3)->format('Ymd');

            $params = [
                'dataFinal' => $dataFinal,
                'pagina' => 1,
                'tamanhoPagina' => 50 // Buscar mais itens de uma vez
            ];

            if ($request->has('uf') && !empty($request->uf)) {
                $params['uf'] = $request->uf;
            }

            if ($request->has('codigoModalidadeContratacao') && !empty($request->codigoModalidadeContratacao)) {
                $params['codigoModalidadeContratacao'] = $request->codigoModalidadeContratacao;
            }

            Log::info('Iniciando sincronização de licitações com parâmetros: ' . json_encode($params));

            $resultado = $this->pncpApiService->consultarLicitacoesAbertas($params);

            $totalRegistros = $resultado['paginacao']['totalRegistros'] ?? 0;

            Log::info('Sincronização concluída com sucesso. Total de registros: ' . $totalRegistros);

            return response()->json([
                'success' => true,
                'message' => 'Sincronização realizada com sucesso!',
                'total_registros' => $totalRegistros
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar licitações: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar licitações: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $licitacao = Licitacao::with(['propostas', 'acompanhamentos'])->findOrFail($id);

        return view('licitacoes.show', [
            'licitacao' => $licitacao
        ]);
    }

    public function marcarInteresse(Request $request, $id)
    {
        $licitacao = Licitacao::findOrFail($id);
        $licitacao->interesse = $request->interesse ? true : false;
        $licitacao->save();

        return response()->json([
            'success' => true,
            'message' => 'Status de interesse atualizado com sucesso!'
        ]);
    }

    public function marcarAnalisada(Request $request, $id)
    {
        $licitacao = Licitacao::findOrFail($id);
        $licitacao->analisada = $request->analisada ? true : false;
        $licitacao->save();

        return response()->json([
            'success' => true,
            'message' => 'Status de análise atualizado com sucesso!'
        ]);
    }
}
