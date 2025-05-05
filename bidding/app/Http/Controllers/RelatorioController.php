<?php

namespace App\Http\Controllers;

use App\Models\Licitacao;
use App\Models\Proposta;
use App\Exports\RelatorioExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index');
    }

    public function licitacoesPorPeriodo(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        $licitacoes = Licitacao::whereBetween('data_publicacao_pncp', [$dataInicio, $dataFim])
                              ->orderBy('data_publicacao_pncp', 'desc')
                              ->get();

        // Agrupar por dia para o gráfico
        $licitacoesPorDia = $licitacoes->groupBy(function ($licitacao) {
            return $licitacao->data_publicacao_pncp->format('Y-m-d');
        })->map(function ($grupo) {
            return $grupo->count();
        });

        return response()->json([
            'licitacoes' => $licitacoes,
            'licitacoesPorDia' => $licitacoesPorDia,
            'total' => $licitacoes->count()
        ]);
    }

    public function propostasPorStatus(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        $propostas = Proposta::whereBetween('created_at', [$dataInicio, $dataFim])
                            ->select('status', DB::raw('count(*) as total'))
                            ->groupBy('status')
                            ->get();

        return response()->json($propostas);
    }

    public function desempenhoPorCliente(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        $desempenho = DB::table('propostas')
                      ->join('clientes', 'propostas.cliente_id', '=', 'clientes.id')
                      ->whereBetween('propostas.created_at', [$dataInicio, $dataFim])
                      ->select(
                          'clientes.id',
                          'clientes.nome',
                          DB::raw('count(propostas.id) as total_propostas'),
                          DB::raw('count(case when propostas.status = "vencedora" then 1 end) as vencedoras'),
                          DB::raw('count(case when propostas.status = "rejeitada" then 1 end) as rejeitadas'),
                          DB::raw('count(case when propostas.status = "aceita" then 1 end) as aceitas'),
                          DB::raw('sum(propostas.valor_proposta) as valor_total')
                      )
                      ->groupBy('clientes.id', 'clientes.nome')
                      ->orderBy('total_propostas', 'desc')
                      ->get();

        return response()->json($desempenho);
    }

    public function licitacoesPorUF(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        $licitacoesPorUF = Licitacao::whereBetween('data_publicacao_pncp', [$dataInicio, $dataFim])
                                   ->select('uf', DB::raw('count(*) as total'))
                                   ->groupBy('uf')
                                   ->orderBy('total', 'desc')
                                   ->get();

        return response()->json($licitacoesPorUF);
    }

    public function valorMedioPorModalidade(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        $valorMedio = Licitacao::whereBetween('data_publicacao_pncp', [$dataInicio, $dataFim])
                              ->select('modalidade_nome', DB::raw('avg(valor_total_estimado) as valor_medio'), DB::raw('count(*) as total'))
                              ->groupBy('modalidade_nome')
                              ->orderBy('valor_medio', 'desc')
                              ->get();

        return response()->json($valorMedio);
    }

    public function exportarExcel(Request $request, $tipo)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        switch ($tipo) {
            case 'licitacoesPorPeriodo':
                return $this->exportarLicitacoesPorPeriodo($dataInicio, $dataFim);
            case 'propostasPorStatus':
                return $this->exportarPropostasPorStatus($dataInicio, $dataFim);
            case 'desempenhoPorCliente':
                return $this->exportarDesempenhoPorCliente($dataInicio, $dataFim);
            case 'licitacoesPorUF':
                return $this->exportarLicitacoesPorUF($dataInicio, $dataFim);
            case 'valorMedioPorModalidade':
                return $this->exportarValorMedioPorModalidade($dataInicio, $dataFim);
            default:
                return redirect()->back()->with('error', 'Tipo de relatório inválido.');
        }
    }

    private function exportarLicitacoesPorPeriodo($dataInicio, $dataFim)
    {
        $licitacoes = Licitacao::whereBetween('data_publicacao_pncp', [$dataInicio, $dataFim])
                              ->orderBy('data_publicacao_pncp', 'desc')
                              ->get();

        $dados = $licitacoes->map(function ($licitacao) {
            return [
                'numero_controle_pncp' => $licitacao->numero_controle_pncp,
                'orgao_entidade' => $licitacao->orgao_entidade,
                'modalidade_nome' => $licitacao->modalidade_nome,
                'objeto_compra' => $licitacao->objeto_compra,
                'valor_total_estimado' => $licitacao->valor_total_estimado,
                'data_publicacao_pncp' => $licitacao->data_publicacao_pncp->format('d/m/Y'),
                'data_encerramento_proposta' => $licitacao->data_encerramento_proposta->format('d/m/Y H:i'),
                'uf' => $licitacao->uf,
                'situacao_compra_nome' => $licitacao->situacao_compra_nome,
                'interesse' => $licitacao->interesse ? 'Sim' : 'Não'
            ];
        });

        $cabecalho = [
            'Nº Controle PNCP',
            'Órgão',
            'Modalidade',
            'Objeto',
            'Valor Estimado',
            'Data Publicação',
            'Data Encerramento',
            'UF',
            'Situação',
            'Interesse'
        ];

        $titulo = 'Licitações por Período';

        return Excel::download(new RelatorioExport($dados, $cabecalho, $titulo), 'licitacoes_por_periodo.xlsx');
    }

    private function exportarPropostasPorStatus($dataInicio, $dataFim)
    {
        $propostas = Proposta::whereBetween('created_at', [$dataInicio, $dataFim])
                            ->select('status', DB::raw('count(*) as total'))
                            ->groupBy('status')
                            ->get();

        $dados = $propostas->map(function ($proposta) {
            return [
                'status' => $this->getStatusLabel($proposta->status),
                'total' => $proposta->total
            ];
        });

        $cabecalho = [
            'Status',
            'Quantidade'
        ];

        $titulo = 'Propostas por Status';

        return Excel::download(new RelatorioExport($dados, $cabecalho, $titulo), 'propostas_por_status.xlsx');
    }

    private function exportarDesempenhoPorCliente($dataInicio, $dataFim)
    {
        $desempenho = DB::table('propostas')
                      ->join('clientes', 'propostas.cliente_id', '=', 'clientes.id')
                      ->whereBetween('propostas.created_at', [$dataInicio, $dataFim])
                      ->select(
                          'clientes.nome',
                          DB::raw('count(propostas.id) as total_propostas'),
                          DB::raw('count(case when propostas.status = "vencedora" then 1 end) as vencedoras'),
                          DB::raw('count(case when propostas.status = "rejeitada" then 1 end) as rejeitadas'),
                          DB::raw('count(case when propostas.status = "aceita" then 1 end) as aceitas'),
                          DB::raw('sum(propostas.valor_proposta) as valor_total')
                      )
                      ->groupBy('clientes.nome')
                      ->orderBy('total_propostas', 'desc')
                      ->get();

        $dados = $desempenho->map(function ($item) {
            return [
                'nome' => $item->nome,
                'total_propostas' => $item->total_propostas,
                'vencedoras' => $item->vencedoras,
                'rejeitadas' => $item->rejeitadas,
                'aceitas' => $item->aceitas,
                'valor_total' => $item->valor_total
            ];
        });

        $cabecalho = [
            'Cliente',
            'Total Propostas',
            'Vencedoras',
            'Rejeitadas',
            'Aceitas',
            'Valor Total'
        ];

        $titulo = 'Desempenho por Cliente';

        return Excel::download(new RelatorioExport($dados, $cabecalho, $titulo), 'desempenho_por_cliente.xlsx');
    }

    private function exportarLicitacoesPorUF($dataInicio, $dataFim)
    {
        $licitacoesPorUF = Licitacao::whereBetween('data_publicacao_pncp', [$dataInicio, $dataFim])
                                   ->select('uf', DB::raw('count(*) as total'))
                                   ->groupBy('uf')
                                   ->orderBy('total', 'desc')
                                   ->get();

        $dados = $licitacoesPorUF->map(function ($item) {
            return [
                'uf' => $item->uf,
                'total' => $item->total
            ];
        });

        $cabecalho = [
            'UF',
            'Quantidade'
        ];

        $titulo = 'Licitações por UF';

        return Excel::download(new RelatorioExport($dados, $cabecalho, $titulo), 'licitacoes_por_uf.xlsx');
    }

    private function exportarValorMedioPorModalidade($dataInicio, $dataFim)
    {
        $valorMedio = Licitacao::whereBetween('data_publicacao_pncp', [$dataInicio, $dataFim])
                              ->select('modalidade_nome', DB::raw('avg(valor_total_estimado) as valor_medio'), DB::raw('count(*) as total'))
                              ->groupBy('modalidade_nome')
                              ->orderBy('valor_medio', 'desc')
                              ->get();

        $dados = $valorMedio->map(function ($item) {
            return [
                'modalidade_nome' => $item->modalidade_nome,
                'valor_medio' => $item->valor_medio,
                'total' => $item->total
            ];
        });

        $cabecalho = [
            'Modalidade',
            'Valor Médio',
            'Quantidade'
        ];

        $titulo = 'Valor Médio por Modalidade';

        return Excel::download(new RelatorioExport($dados, $cabecalho, $titulo), 'valor_medio_por_modalidade.xlsx');
    }

    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'elaboracao':
                return 'Em Elaboração';
            case 'enviada':
                return 'Enviada';
            case 'aceita':
                return 'Aceita';
            case 'rejeitada':
                return 'Rejeitada';
            case 'vencedora':
                return 'Vencedora';
            default:
                return 'Desconhecido';
        }
    }
}
