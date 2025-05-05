<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Licitacao;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class PncpApiService
{
    public function consultarLicitacoesAbertas($params = [])
    {
        try {
            $client = new Client([
                'timeout' => 60,
                'http_errors' => false
            ]);

            // Data padrão: próximos 3 meses no formato YYYYMMDD
            $dataFormatada = Carbon::now()->addMonths(3)->format('Ymd');

            // Construir os parâmetros exatamente como nos testes que deram certo
            $queryParams = [
                'dataFinal' => $params['dataFinal'] ?? $dataFormatada,
                'pagina' => $params['pagina'] ?? 1
            ];

            // Adicionar tamanhoPagina se fornecido
            if (isset($params['tamanhoPagina'])) {
                $queryParams['tamanhoPagina'] = $params['tamanhoPagina'];
            } else {
                $queryParams['tamanhoPagina'] = 10; // Valor padrão
            }

            // Construir a URL completa exatamente como nos testes que funcionaram
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

            if (!isset($data['data']) || empty($data['data'])) {
                return [
                    'licitacoes' => [],
                    'paginacao' => [
                        'totalRegistros' => 0,
                        'totalPaginas' => 0,
                        'paginaAtual' => $queryParams['pagina']
                    ]
                ];
            }

            $this->processarLicitacoes($data['data']);

            return [
                'licitacoes' => $data['data'],
                'paginacao' => [
                    'totalRegistros' => $data['totalRegistros'] ?? 0,
                    'totalPaginas' => $data['totalPaginas'] ?? 0,
                    'paginaAtual' => $queryParams['pagina']
                ]
            ];
        } catch (Exception $e) {
            Log::error('Erro ao consultar licitações: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processarLicitacoes($licitacoes)
    {
        foreach ($licitacoes as $licitacao) {
            try {
                // Verifica se a licitação já existe no banco
                $existingLicitacao = Licitacao::where('numero_controle_pncp', $licitacao['numeroControlePNCP'])->first();

                if (!$existingLicitacao) {
                    Log::info('Processando nova licitação: ' . $licitacao['numeroControlePNCP']);

                    // Tratamento seguro dos dados aninhados
                    $orgao = isset($licitacao['orgaoEntidade']) ? ($licitacao['orgaoEntidade']['razaoSocial'] ?? '') : '';
                    $unidade = isset($licitacao['unidadeOrgao']) ? ($licitacao['unidadeOrgao']['nomeUnidade'] ?? '') : '';
                    $uf = isset($licitacao['unidadeOrgao']) ? ($licitacao['unidadeOrgao']['ufSigla'] ?? '') : '';
                    $municipio = isset($licitacao['unidadeOrgao']) ? ($licitacao['unidadeOrgao']['municipioNome'] ?? '') : '';
                    $cnpj = isset($licitacao['orgaoEntidade']) ? ($licitacao['orgaoEntidade']['cnpj'] ?? '') : '';

                    // Tratamento seguro das datas
                    try {
                        $dataInclusao = !empty($licitacao['dataInclusao']) ? Carbon::parse($licitacao['dataInclusao']) : null;
                    } catch (\Exception $e) {
                        $dataInclusao = null;
                        Log::warning('Erro ao converter data de inclusão: ' . $e->getMessage());
                    }

                    try {
                        $dataPublicacao = !empty($licitacao['dataPublicacaoPncp']) ? Carbon::parse($licitacao['dataPublicacaoPncp']) : null;
                    } catch (\Exception $e) {
                        $dataPublicacao = null;
                        Log::warning('Erro ao converter data de publicação: ' . $e->getMessage());
                    }

                    try {
                        $dataAbertura = !empty($licitacao['dataAberturaProposta']) ? Carbon::parse($licitacao['dataAberturaProposta']) : null;
                    } catch (\Exception $e) {
                        $dataAbertura = null;
                        Log::warning('Erro ao converter data de abertura: ' . $e->getMessage());
                    }

                    try {
                        $dataEncerramento = !empty($licitacao['dataEncerramentoProposta']) ? Carbon::parse($licitacao['dataEncerramentoProposta']) : null;
                    } catch (\Exception $e) {
                        $dataEncerramento = null;
                        Log::warning('Erro ao converter data de encerramento: ' . $e->getMessage());
                    }

                    // Cria nova licitação
                    Licitacao::create([
                        'numero_controle_pncp' => $licitacao['numeroControlePNCP'] ?? '',
                        'orgao_entidade' => $orgao,
                        'unidade_orgao' => $unidade,
                        'ano_compra' => $licitacao['anoCompra'] ?? 0,
                        'sequencial_compra' => $licitacao['sequencialCompra'] ?? 0,
                        'numero_compra' => $licitacao['numeroCompra'] ?? '',
                        'objeto_compra' => $licitacao['objetoCompra'] ?? '',
                        'modalidade_nome' => $licitacao['modalidadeNome'] ?? '',
                        'modo_disputa_nome' => $licitacao['modoDisputaNome'] ?? '',
                        'valor_total_estimado' => $licitacao['valorTotalEstimado'] ?? 0,
                        'situacao_compra_nome' => $licitacao['situacaoCompraNome'] ?? '',
                        'data_inclusao' => $dataInclusao,
                        'data_publicacao_pncp' => $dataPublicacao,
                        'data_abertura_proposta' => $dataAbertura,
                        'data_encerramento_proposta' => $dataEncerramento,
                        'link_sistema_origem' => $licitacao['linkSistemaOrigem'] ?? '',
                        'is_srp' => $licitacao['srp'] ?? false,
                        'uf' => $uf,
                        'municipio' => $municipio,
                        'cnpj' => $cnpj,
                        'analisada' => false,
                        'interesse' => false
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Erro ao processar licitação: ' . $e->getMessage());
                Log::error('Dados da licitação que causou erro: ' . json_encode($licitacao));
                // Continua para a próxima licitação mesmo se houver erro
                continue;
            }
        }
    }
}
