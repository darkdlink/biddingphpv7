<?php

namespace App\Http\Controllers;

use App\Models\Licitacao;
use App\Services\PncpApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LicitacaoController extends Controller
{
    protected $pncpApiService;

    public function __construct(PncpApiService $pncpApiService)
    {
        $this->pncpApiService = $pncpApiService;
    }

    public function index(Request $request)
    {
        try {
            // Log para debug
            \Illuminate\Support\Facades\Log::info('Acessando index de licitações');

            // Verifica quantas licitações existem no total
            $total = \App\Models\Licitacao::count();
            \Illuminate\Support\Facades\Log::info('Total de licitações no banco: ' . $total);

            // Busca básica sem filtros para verificação
            $todasLicitacoes = \App\Models\Licitacao::limit(10)->get();
            \Illuminate\Support\Facades\Log::info('Primeiras 10 licitações: ' . json_encode($todasLicitacoes));

            // Consulta principal com filtros
            $licitacoes = \App\Models\Licitacao::query();

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

            // Ordenação (por default, usar created_at para mostrar as mais recentes)
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');

            $licitacoes = $licitacoes->orderBy($sortField, $sortDirection);

            // Paginação
            $licitacoesPaginadas = $licitacoes->paginate(15);

            \Illuminate\Support\Facades\Log::info('Licitações após filtros e paginação: ' . $licitacoesPaginadas->count());

            // Retornar a view com as licitações
            return view('licitacoes.index', [
                'licitacoes' => $licitacoesPaginadas,
                'filtros' => $request->all()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao acessar index de licitações: ' . $e->getMessage());

            return view('licitacoes.index', [
                'licitacoes' => collect(),
                'filtros' => $request->all(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sincronizar(Request $request)
    {
        try {
            Log::info('Iniciando sincronização de licitações');

            // Data padrão: próximos 3 meses
            $dataFinal = Carbon::now()->addMonths(3)->format('Ymd');

            $params = [
                'dataFinal' => $dataFinal,
                'pagina' => 1,
                'tamanhoPagina' => 20 // Reduzir para teste
            ];

            if ($request->has('uf') && !empty($request->uf)) {
                $params['uf'] = $request->uf;
            }

            if ($request->has('codigoModalidadeContratacao') && !empty($request->codigoModalidadeContratacao)) {
                $params['codigoModalidadeContratacao'] = $request->codigoModalidadeContratacao;
            }

            // Limpar cache antes da sincronização
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            // Licitacao::truncate(); // Descomente se quiser limpar todas as licitações a cada sincronização
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Log::info('Chamando serviço de API com parâmetros: ' . json_encode($params));

            $resultado = $this->pncpApiService->consultarLicitacoesAbertas($params);

            $totalRegistros = $resultado['paginacao']['totalRegistros'] ?? 0;

            // Verificar se as licitações foram salvas
            $countAfter = Licitacao::count();
            Log::info('Total de licitações após sincronização: ' . $countAfter);

            Log::info('Sincronização concluída com sucesso. Total de registros na API: ' . $totalRegistros);

            return response()->json([
                'success' => true,
                'message' => 'Sincronização realizada com sucesso!',
                'total_registros' => $totalRegistros,
                'licitacoes_salvas' => $countAfter
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar licitações: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

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
