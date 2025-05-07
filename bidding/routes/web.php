<?php

use App\Http\Controllers\LicitacaoController;
use App\Http\Controllers\PropostaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiTestController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\UserController;



// Rota para a página inicial
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Rotas para licitações
Route::get('/licitacoes', [LicitacaoController::class, 'index'])->name('licitacoes.index');
Route::get('/licitacoes/sincronizar', [LicitacaoController::class, 'sincronizar'])->name('licitacoes.sincronizar');
Route::get('/licitacoes/{id}', [LicitacaoController::class, 'show'])->name('licitacoes.show');
Route::post('/licitacoes/{id}/interesse', [LicitacaoController::class, 'marcarInteresse'])->name('licitacoes.interesse');
Route::post('/licitacoes/{id}/analisada', [LicitacaoController::class, 'marcarAnalisada'])->name('licitacoes.analisada');

// Rotas para propostas
Route::resource('propostas', PropostaController::class);

// Rotas para clientes
Route::resource('clientes', ClienteController::class);

// Rotas de autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Aplicar middleware de autenticação para as rotas que requerem login
Route::middleware(['auth'])->group(function () {
    // Rota para a página inicial
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas para licitações
    Route::get('/licitacoes', [LicitacaoController::class, 'index'])->name('licitacoes.index');
    Route::get('/licitacoes/sincronizar', [LicitacaoController::class, 'sincronizar'])->name('licitacoes.sincronizar');
    Route::get('/licitacoes/{id}', [LicitacaoController::class, 'show'])->name('licitacoes.show');
    Route::post('/licitacoes/{id}/interesse', [LicitacaoController::class, 'marcarInteresse'])->name('licitacoes.interesse');
    Route::post('/licitacoes/{id}/analisada', [LicitacaoController::class, 'marcarAnalisada'])->name('licitacoes.analisada');

    // Rotas para propostas
    Route::resource('propostas', PropostaController::class);
    Route::post('/propostas/{id}/enviar', [PropostaController::class, 'enviar'])->name('propostas.enviar');
    Route::post('/propostas/{id}/status', [PropostaController::class, 'atualizarStatus'])->name('propostas.status');

    // Rotas para clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes/lista', [ClienteController::class, 'lista'])->name('clientes.lista');
});


// Rotas para relatórios
Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
Route::get('/relatorios/licitacoesPorPeriodo', [RelatorioController::class, 'licitacoesPorPeriodo']);
Route::get('/relatorios/propostasPorStatus', [RelatorioController::class, 'propostasPorStatus']);
Route::get('/relatorios/desempenhoPorCliente', [RelatorioController::class, 'desempenhoPorCliente']);
Route::get('/relatorios/licitacoesPorUF', [RelatorioController::class, 'licitacoesPorUF']);
Route::get('/relatorios/valorMedioPorModalidade', [RelatorioController::class, 'valorMedioPorModalidade']);
Route::get('/relatorios/{tipo}/excel', [RelatorioController::class, 'exportarExcel']);

// Rotas de teste da API
Route::get('/api-test', [ApiTestController::class, 'index'])->name('api.test');
Route::post('/api-test/connection', [ApiTestController::class, 'testApiConnection'])->name('api.test.connection');

Route::get('/api-test/multiple-endpoints', [ApiTestController::class, 'testMultipleEndpoints'])->name('api.test.multiple');

Route::get('/api-test/specific-url', [ApiTestController::class, 'testApiWithSpecificUrl'])->name('api.test.specific');

Route::get('/diagnostico-licitacoes', function() {
    $count = \App\Models\Licitacao::count();
    $recentes = \App\Models\Licitacao::latest('created_at')->take(5)->get();

    return [
        'total' => $count,
        'recentes' => $recentes
    ];
});

Route::get('/verificar-banco', function () {
    // Verificar se a tabela existe
    $tabelaExiste = Schema::hasTable('licitacoes');

    $resultado = [
        'tabela_existe' => $tabelaExiste
    ];

    if ($tabelaExiste) {
        // Verificar colunas
        $colunas = Schema::getColumnListing('licitacoes');
        $resultado['colunas'] = $colunas;

        // Verificar conexão
        try {
            DB::connection()->getPdo();
            $resultado['conexao'] = 'OK - ' . DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            $resultado['conexao'] = 'ERRO - ' . $e->getMessage();
        }

        // Tentar inserir um registro de teste
        try {
            $teste = new \App\Models\Licitacao();
            $teste->numero_controle_pncp = 'TESTE-' . time();
            $teste->orgao_entidade = 'TESTE';
            $teste->ano_compra = 2023;
            $teste->sequencial_compra = 1;
            $teste->numero_compra = 'TESTE-001';
            $teste->objeto_compra = 'Teste de inserção';
            $teste->modalidade_nome = 'TESTE';
            $teste->valor_total_estimado = 1000;
            $teste->situacao_compra_nome = 'Teste';
            $teste->is_srp = false;
            $teste->uf = 'TE';
            $teste->cnpj = '00000000000000';
            $teste->analisada = false;
            $teste->interesse = false;

            $resultado['insercao_teste'] = $teste->save();
            $resultado['id_teste'] = $teste->id ?? null;
        } catch (\Exception $e) {
            $resultado['insercao_teste'] = false;
            $resultado['erro_insercao'] = $e->getMessage();
        }
    }

    return response()->json($resultado);
});

Route::get('/diagnostico-completo', function () {
    $resultado = [
        'ambiente' => [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'database_name' => config('database.connections.' . config('database.default') . '.database')
        ],
        'tabelas' => []
    ];

    // Verificar tabelas
    $tabelas = ['licitacoes', 'migrations', 'users'];
    foreach ($tabelas as $tabela) {
        $resultado['tabelas'][$tabela] = [
            'existe' => Schema::hasTable($tabela)
        ];

        if (Schema::hasTable($tabela)) {
            $resultado['tabelas'][$tabela]['colunas'] = Schema::getColumnListing($tabela);

            if ($tabela == 'licitacoes') {
                try {
                    $resultado['tabelas'][$tabela]['registros'] = DB::table($tabela)->count();
                    $resultado['tabelas'][$tabela]['exemplo'] = DB::table($tabela)->first();
                } catch (\Exception $e) {
                    $resultado['tabelas'][$tabela]['erro'] = $e->getMessage();
                }
            }
        }
    }

    // Testar conexão e inserção
    try {
        $testeInsercao = new \App\Models\Licitacao();
        $testeInsercao->numero_controle_pncp = 'TESTE-DIAGNOSTICO-' . time();
        $testeInsercao->orgao_entidade = 'TESTE DIAGNÓSTICO';
        $testeInsercao->ano_compra = 2023;
        $testeInsercao->sequencial_compra = 1;
        $testeInsercao->numero_compra = 'TESTE-DIAG-001';
        $testeInsercao->objeto_compra = 'Teste de diagnóstico';
        $testeInsercao->modalidade_nome = 'TESTE';
        $testeInsercao->valor_total_estimado = 1000;
        $testeInsercao->situacao_compra_nome = 'Teste';
        $testeInsercao->is_srp = false;
        $testeInsercao->uf = 'TE';
        $testeInsercao->cnpj = '00000000000000';
        $testeInsercao->analisada = false;
        $testeInsercao->interesse = false;

        $resultado['teste_insercao'] = [
            'sucesso' => $testeInsercao->save(),
            'id' => $testeInsercao->id ?? null
        ];
    } catch (\Exception $e) {
        $resultado['teste_insercao'] = [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    }

    return response()->json($resultado);
});


Route::get('/debug-licitacoes', function() {
    $licitacoes = \App\Models\Licitacao::all();

    return [
        'total' => $licitacoes->count(),
        'licitacoes' => $licitacoes
    ];
});

// Rotas para propostas
Route::get('/propostas', [PropostaController::class, 'index'])->name('propostas.index');
Route::get('/propostas/create', [PropostaController::class, 'create'])->name('propostas.create');
Route::post('/propostas', [PropostaController::class, 'store'])->name('propostas.store');
Route::get('/propostas/{id}', [PropostaController::class, 'show'])->name('propostas.show');
Route::get('/propostas/{id}/edit', [PropostaController::class, 'edit'])->name('propostas.edit');
Route::put('/propostas/{id}', [PropostaController::class, 'update'])->name('propostas.update');
Route::delete('/propostas/{id}', [PropostaController::class, 'destroy'])->name('propostas.destroy');
Route::post('/propostas/{id}/enviar', [PropostaController::class, 'enviar'])->name('propostas.enviar');

// Rotas para usuários
Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');

// Rotas para configurações
Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
Route::post('/configuracoes', [ConfiguracaoController::class, 'store'])->name('configuracoes.store');
Route::post('/configuracoes/testar-email', [ConfiguracaoController::class, 'testarEmail'])->name('configuracoes.testar-email');
Route::post('/configuracoes/backup/gerar', [ConfiguracaoController::class, 'gerarBackup'])->name('configuracoes.backup.gerar');
Route::get('/configuracoes/backup/download/{id}', [ConfiguracaoController::class, 'downloadBackup'])->name('configuracoes.backup.download');
Route::post('/configuracoes/backup/restaurar', [ConfiguracaoController::class, 'restaurarBackup'])->name('configuracoes.backup.restaurar');
Route::delete('/configuracoes/backup/excluir/{id}', [ConfiguracaoController::class, 'excluirBackup'])->name('configuracoes.backup.excluir');

// Rota para busca de clientes
Route::get('/clientes/search', [App\Http\Controllers\ClienteController::class, 'search'])->name('clientes.search');

// Rotas de recursos para clientes
Route::resource('clientes', App\Http\Controllers\ClienteController::class);

// Rota para obter clientes em formato JSON (para uso em requisições AJAX)
Route::get('/clientes/json', [App\Http\Controllers\ClienteController::class, 'getClientesJson'])->name('clientes.json');

// Rotas para filtro por segmento
Route::get('/licitacoes/segmento', [App\Http\Controllers\LicitacaoController::class, 'segmento'])->name('licitacoes.segmento');
Route::post('/licitacoes/sincronizar/segmento', [App\Http\Controllers\LicitacaoController::class, 'sincronizarPorSegmento'])->name('licitacoes.sincronizar.segmento');