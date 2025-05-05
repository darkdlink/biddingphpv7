<?php

namespace App\Http\Controllers;

use App\Models\Licitacao;
use App\Models\Proposta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Total de licitações cadastradas
        $totalLicitacoes = Licitacao::count();

        // Total de licitações em andamento (com propostas ainda abertas)
        $licitacoesEmAndamento = Licitacao::where('data_encerramento_proposta', '>=', Carbon::now())->count();

        // Total de licitações marcadas com interesse
        $licitacoesInteresse = Licitacao::where('interesse', true)->count();

        // Total de propostas
        $totalPropostas = Proposta::count();

        // Propostas por status
        $propostasPorStatus = [
            'elaboracao' => Proposta::where('status', 'elaboracao')->count(),
            'enviada' => Proposta::where('status', 'enviada')->count(),
            'aceita' => Proposta::where('status', 'aceita')->count(),
            'rejeitada' => Proposta::where('status', 'rejeitada')->count(),
            'vencedora' => Proposta::where('status', 'vencedora')->count()
        ];

        // Licitações que encerram nos próximos 7 dias
        $proximasLicitacoes = Licitacao::where('data_encerramento_proposta', '>=', Carbon::now())
                                       ->where('data_encerramento_proposta', '<=', Carbon::now()->addDays(7))
                                       ->where('interesse', true)
                                       ->orderBy('data_encerramento_proposta', 'asc')
                                       ->take(5)
                                       ->get();

        // Últimas propostas enviadas
        $ultimasPropostas = Proposta::with(['licitacao', 'cliente'])
                                   ->whereNotNull('data_envio')
                                   ->orderBy('data_envio', 'desc')
                                   ->take(5)
                                   ->get();

        // Licitações por UF (para gráfico)
        $licitacoesPorUF = Licitacao::selectRaw('uf, count(*) as total')
                                   ->groupBy('uf')
                                   ->orderByRaw('count(*) desc')
                                   ->take(10)
                                   ->get();

        return view('dashboard', [
            'totalLicitacoes' => $totalLicitacoes,
            'licitacoesEmAndamento' => $licitacoesEmAndamento,
            'licitacoesInteresse' => $licitacoesInteresse,
            'totalPropostas' => $totalPropostas,
            'propostasPorStatus' => $propostasPorStatus,
            'proximasLicitacoes' => $proximasLicitacoes,
            'ultimasPropostas' => $ultimasPropostas,
            'licitacoesPorUF' => $licitacoesPorUF
        ]);
    }
}
