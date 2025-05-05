<?php

namespace App\Console\Commands;

use App\Services\PncpApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SincronizarLicitacoes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licitacoes:sincronizar {--uf=} {--modalidade=} {--dias=90}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza licitações com a API do PNCP';

    /**
     * @var PncpApiService
     */
    protected $apiService;

    /**
     * Create a new command instance.
     *
     * @param PncpApiService $apiService
     * @return void
     */
    public function __construct(PncpApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Iniciando sincronização de licitações...');

            $params = [
                'dataFinal' => Carbon::now()->addDays($this->option('dias'))->format('Y-m-d')
            ];

            if ($this->option('uf')) {
                $params['uf'] = $this->option('uf');
                $this->info('Filtrando por UF: ' . $this->option('uf'));
            }

            if ($this->option('modalidade')) {
                $params['codigoModalidadeContratacao'] = $this->option('modalidade');
                $this->info('Filtrando por modalidade: ' . $this->option('modalidade'));
            }

            $resultado = $this->apiService->consultarLicitacoesAbertas($params);

            $this->info('Sincronização concluída com sucesso!');
            $this->info('Total de registros: ' . $resultado['paginacao']['totalRegistros']);

            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao sincronizar licitações: ' . $e->getMessage());
            Log::error('Erro ao sincronizar licitações: ' . $e->getMessage());

            return 1;
        }
    }
}
