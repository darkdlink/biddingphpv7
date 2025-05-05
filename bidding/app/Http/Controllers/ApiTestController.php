<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiTestController extends Controller
{
    public function index()
    {
        return view('api_test.index');
    }

    public function testApiConnection(Request $request)
    {
        try {
            $baseUrl = $request->input('base_url', 'https://pncp.gov.br/api/consulta/v1');
            $endpoint = $request->input('endpoint', '/contratacoes/proposta');
            $dataFinal = $request->input('data_final', date('Ymd'));
            $pagina = $request->input('pagina', 1);
            $tamanhoPagina = $request->input('tamanho_pagina', 10);

            // Configurar cliente HTTP
            $client = new Client([
                'timeout' => 30,
                'http_errors' => false,
            ]);

            // Construir a URL completa
            $url = $baseUrl . $endpoint . '?dataFinal=' . $dataFinal . '&pagina=' . $pagina;
            if ($tamanhoPagina) {
                $url .= '&tamanhoPagina=' . $tamanhoPagina;
            }

            // Registrar detalhes da requisição para debugging
            Log::info('Teste de API - URL completa: ' . $url);

            // Realizar a requisição
            $startTime = microtime(true);
            $response = $client->request('GET', $url);
            $endTime = microtime(true);

            // Analisar a resposta
            $statusCode = $response->getStatusCode();
            $body = (string)$response->getBody();
            $responseTime = round(($endTime - $startTime) * 1000); // em milissegundos
            $headers = $response->getHeaders();

            // Decodificar o JSON (se aplicável)
            $jsonData = json_decode($body, true);
            $jsonError = json_last_error();

            return response()->json([
                'success' => $statusCode >= 200 && $statusCode < 300,
                'message' => 'Teste realizado com sucesso',
                'status_code' => $statusCode,
                'response_time' => $responseTime . 'ms',
                'json_valid' => $jsonError === JSON_ERROR_NONE,
                'data_count' => isset($jsonData['data']) ? count($jsonData['data']) : 0,
                'total_records' => $jsonData['totalRegistros'] ?? 0,
                'request_details' => [
                    'url' => $url
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar com a API',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testApiWithSpecificUrl()
    {
        try {
            // URL específica que sabemos que funciona
            $url = 'https://pncp.gov.br/api/consulta/v1/contratacoes/proposta?dataFinal=20250520&pagina=1';

            // Configurar cliente HTTP
            $client = new Client([
                'timeout' => 30,
                'http_errors' => false,
            ]);

            // Realizar a requisição
            $startTime = microtime(true);
            $response = $client->request('GET', $url);
            $endTime = microtime(true);

            // Analisar a resposta
            $statusCode = $response->getStatusCode();
            $body = (string)$response->getBody();
            $responseTime = round(($endTime - $startTime) * 1000); // em milissegundos

            // Decodificar o JSON (se aplicável)
            $jsonData = json_decode($body, true);
            $jsonError = json_last_error();

            // View específica para mostrar o resultado do teste
            return view('api_test.specific_url', [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status_code' => $statusCode,
                'response_time' => $responseTime,
                'json_valid' => $jsonError === JSON_ERROR_NONE,
                'data_count' => isset($jsonData['data']) ? count($jsonData['data']) : 0,
                'total_records' => $jsonData['totalRegistros'] ?? 0,
                'url' => $url,
                'response_preview' => substr($body, 0, 1000)
            ]);
        } catch (\Exception $e) {
            return view('api_test.specific_url', [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => 'https://pncp.gov.br/api/consulta/v1/contratacoes/proposta?dataFinal=20250520&pagina=1'
            ]);
        }
    }
}
