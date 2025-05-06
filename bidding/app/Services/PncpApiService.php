<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Licitacao;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PncpApiService
{
    public function consultarLicitacoesAbertas($params = [])
    {
        try {
            // Limpar e configurar o log para debugging
            Log::info('==== INÍCIO DA SINCRONIZAÇÃO DE LICITAÇÕES ====');
            Log::info('Parâmetros recebidos: ' . json_encode($params));

            $client = new Client([
                'timeout' => 90, // Aumentar timeout para 90 segundos
                'http_errors' => false
            ]);

            // Data padrão: próximos 3 meses no formato YYYYMMDD
            $dataFormatada = Carbon::now()->addMonths(3)->format('Ymd');

            // Construir os parâmetros
            $queryParams = [
                'dataFinal' => $params['dataFinal'] ?? $dataFormatada,
                'pagina' => $params['pagina'] ?? 1,
                'tamanhoPagina' => $params['tamanhoPagina'] ?? 10
            ];

            // Construir a URL completa
            $url = 'https://pncp.gov.br/api/consulta/v1/contratacoes/proposta?';
            $url .= 'dataFinal=' . $queryParams['dataFinal'];
            $url .= '&pagina=' . $queryParams['pagina'];
            $url .= '&tamanhoPagina=' . $queryParams['tamanhoPagina'];

            Log::info('Consultando API com URL: ' . $url);

            // Realizar a requisição com a URL completa
            $response = $client->request('GET', $url);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            Log::info('Resposta da API recebida. Status: ' . $statusCode);

            if ($statusCode >= 400) {
                Log::error('Erro na API do PNCP. Status: ' . $statusCode);
                Log::error('Resposta: ' . substr($body, 0, 1000));
                throw new Exception('Erro na API do PNCP. Status: ' . $statusCode);
            }

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro ao decodificar JSON: ' . json_last_error_msg());
                Log::error('Corpo da resposta: ' . substr($body, 0, 1000));
                throw new Exception('Resposta inválida da API (JSON inválido)');
            }

            Log::info('JSON decodificado com sucesso. Verificando dados...');

            if (!isset($data['data']) || empty($data['data'])) {
                Log::warning('Nenhum dado encontrado na resposta da API');
                return [
                    'licitacoes' => [],
                    'paginacao' => [
                        'totalRegistros' => 0,
                        'totalPaginas' => 0,
                        'paginaAtual' => $queryParams['pagina']
                    ]
                ];
            }

            Log::info('Dados encontrados na resposta. Total de registros: ' . count($data['data']));

            // Testar processamento de uma licitação como exemplo
            if (count($data['data']) > 0) {
                $exemplo = $data['data'][0];
                Log::info('Exemplo de licitação: ' . json_encode($exemplo));
            }

            // Tenta processar as licitações dentro de uma transação
            $processados = $this->processarLicitacoes($data['data']);

            Log::info('Licitações processadas: ' . $processados);
            Log::info('==== FIM DA SINCRONIZAÇÃO DE LICITAÇÕES ====');

            return [
                'licitacoes' => $data['data'],
                'paginacao' => [
                    'totalRegistros' => $data['totalRegistros'] ?? 0,
                    'totalPaginas' => $data['totalPaginas'] ?? 0,
                    'paginaAtual' => $queryParams['pagina']
                ]
            ];
        } catch (Exception $e) {
            Log::error('ERRO CRÍTICO ao consultar licitações: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    protected function processarLicitacoes($licitacoes)
    {
        $contador = 0;

        try {
            // Usar transação para garantir integridade
            DB::beginTransaction();

            Log::info('Iniciando processamento de ' . count($licitacoes) . ' licitações');

            foreach ($licitacoes as $licitacao) {
                try {
                    // Verifica se a licitação já existe no banco
                    $numeroControle = $licitacao['numeroControlePNCP'] ?? null;

                    if (empty($numeroControle)) {
                        Log::warning('Licitação sem número de controle. Pulando.');
                        continue;
                    }

                    $existingLicitacao = Licitacao::where('numero_controle_pncp', $numeroControle)->first();

                    if (!$existingLicitacao) {
                        Log::info('Processando nova licitação: ' . $numeroControle);

                        // Tratamento seguro dos dados aninhados
                        $orgao = isset($licitacao['orgaoEntidade']) && isset($licitacao['orgaoEntidade']['razaoSocial'])
                            ? $licitacao['orgaoEntidade']['razaoSocial'] : '';

                        $unidade = isset($licitacao['unidadeOrgao']) && isset($licitacao['unidadeOrgao']['nomeUnidade'])
                            ? $licitacao['unidadeOrgao']['nomeUnidade'] : '';

                        $uf = isset($licitacao['unidadeOrgao']) && isset($licitacao['unidadeOrgao']['ufSigla'])
                            ? $licitacao['unidadeOrgao']['ufSigla'] : '';

                        $municipio = isset($licitacao['unidadeOrgao']) && isset($licitacao['unidadeOrgao']['municipioNome'])
                            ? $licitacao['unidadeOrgao']['municipioNome'] : '';

                        $cnpj = isset($licitacao['orgaoEntidade']) && isset($licitacao['orgaoEntidade']['cnpj'])
                            ? $licitacao['orgaoEntidade']['cnpj'] : '';

                        // Tratamento seguro das datas
                        try {
                            $dataInclusao = !empty($licitacao['dataInclusao']) ? Carbon::parse($licitacao['dataInclusao']) : null;
                        } catch (\Exception $e) {
                            Log::warning('Erro ao converter data de inclusão: ' . $e->getMessage());
                            $dataInclusao = null;
                        }

                        try {
                            $dataPublicacao = !empty($licitacao['dataPublicacaoPncp']) ? Carbon::parse($licitacao['dataPublicacaoPncp']) : null;
                        } catch (\Exception $e) {
                            Log::warning('Erro ao converter data de publicação: ' . $e->getMessage());
                            $dataPublicacao = null;
                        }

                        try {
                            $dataAbertura = !empty($licitacao['dataAberturaProposta']) ? Carbon::parse($licitacao['dataAberturaProposta']) : null;
                        } catch (\Exception $e) {
                            Log::warning('Erro ao converter data de abertura: ' . $e->getMessage());
                            $dataAbertura = null;
                        }

                        try {
                            $dataEncerramento = !empty($licitacao['dataEncerramentoProposta']) ? Carbon::parse($licitacao['dataEncerramentoProposta']) : null;
                        } catch (\Exception $e) {
                            Log::warning('Erro ao converter data de encerramento: ' . $e->getMessage());
                            $dataEncerramento = null;
                        }

                        // Valor total estimado com tratamento de erro
                        $valorTotalEstimado = 0;
                        if (isset($licitacao['valorTotalEstimado']) && is_numeric($licitacao['valorTotalEstimado'])) {
                            $valorTotalEstimado = $licitacao['valorTotalEstimado'];
                        }

                        // Log detalhado dos dados que serão inseridos
                        Log::info('Dados para inserção: ' . json_encode([
                            'numero_controle_pncp' => $numeroControle,
                            'orgao_entidade' => $orgao,
                            'unidade_orgao' => $unidade,
                            'uf' => $uf,
                            'valor_total_estimado' => $valorTotalEstimado
                        ]));

                        // Tentativa de inserção
                        $novaLicitacao = new Licitacao();
                        $novaLicitacao->numero_controle_pncp = $numeroControle;
                        $novaLicitacao->orgao_entidade = $orgao;
                        $novaLicitacao->unidade_orgao = $unidade;
                        $novaLicitacao->ano_compra = $licitacao['anoCompra'] ?? 0;
                        $novaLicitacao->sequencial_compra = $licitacao['sequencialCompra'] ?? 0;
                        $novaLicitacao->numero_compra = $licitacao['numeroCompra'] ?? '';
                        $novaLicitacao->objeto_compra = $licitacao['objetoCompra'] ?? '';
                        $novaLicitacao->modalidade_nome = $licitacao['modalidadeNome'] ?? '';
                        $novaLicitacao->modo_disputa_nome = $licitacao['modoDisputaNome'] ?? '';
                        $novaLicitacao->valor_total_estimado = $valorTotalEstimado;
                        $novaLicitacao->situacao_compra_nome = $licitacao['situacaoCompraNome'] ?? '';
                        $novaLicitacao->data_inclusao = $dataInclusao;
                        $novaLicitacao->data_publicacao_pncp = $dataPublicacao;
                        $novaLicitacao->data_abertura_proposta = $dataAbertura;
                        $novaLicitacao->data_encerramento_proposta = $dataEncerramento;
                        $novaLicitacao->link_sistema_origem = $licitacao['linkSistemaOrigem'] ?? '';
                        $novaLicitacao->is_srp = $licitacao['srp'] ?? false;
                        $novaLicitacao->uf = $uf;
                        $novaLicitacao->municipio = $municipio;
                        $novaLicitacao->cnpj = $cnpj;
                        $novaLicitacao->analisada = false;
                        $novaLicitacao->interesse = false;

                        $resultado = $novaLicitacao->save();

                        if ($resultado) {
                            $contador++;
                            Log::info('Licitação salva com sucesso. ID: ' . $novaLicitacao->id);
                        } else {
                            Log::warning('Falha ao salvar a licitação: ' . $numeroControle);
                        }
                    } else {
                        Log::info('Licitação já existe: ' . $numeroControle);
                    }
                } catch (Exception $e) {
                    Log::error('Erro ao processar licitação: ' . $e->getMessage());
                    Log::error('Dados da licitação que causou erro: ' . json_encode($licitacao));
                    // Continua para a próxima licitação mesmo se houver erro
                    continue;
                }
            }

            // Commit da transação
            DB::commit();
            Log::info('Transação concluída com sucesso. Licitações processadas: ' . $contador);

            return $contador;

        } catch (Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();

            Log::error('Erro durante a transação: ' . $e->getMessage());
            throw $e;
        }
    }
}
