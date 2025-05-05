<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class CnpjApiService
{
    protected $client;
    protected $baseUrl = 'https://receitaws.com.br/v1';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
        ]);
    }

    public function consultarCnpj($cnpj)
    {
        try {
            // Remove caracteres especiais do CNPJ
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

            // Verifica se o CNPJ tem 14 dígitos
            if (strlen($cnpj) != 14) {
                throw new Exception('CNPJ inválido.');
            }

            // Realiza a requisição GET
            $response = $this->client->request('GET', '/cnpj/' . $cnpj);

            // Decodifica o corpo da resposta
            $data = json_decode($response->getBody(), true);

            // Verifica se a consulta foi bem-sucedida
            if ($data['status'] != 'OK') {
                throw new Exception('Erro ao consultar CNPJ: ' . ($data['message'] ?? 'Erro desconhecido'));
            }

            // Retorna os dados formatados
            return [
                'nome' => $data['nome'] ?? $data['fantasia'] ?? '',
                'cnpj' => $cnpj,
                'endereco' => ($data['logradouro'] ?? '') . ', ' . ($data['numero'] ?? '') . ' - ' . ($data['complemento'] ?? ''),
                'cidade' => $data['municipio'] ?? '',
                'uf' => $data['uf'] ?? '',
                'cep' => preg_replace('/[^0-9]/', '', $data['cep'] ?? ''),
                'telefone' => $data['telefone'] ?? '',
                'email' => $data['email'] ?? '',
            ];
        } catch (Exception $e) {
            Log::error('Erro ao consultar CNPJ: ' . $e->getMessage());
            throw $e;
        }
    }
}
