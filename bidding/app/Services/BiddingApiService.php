<?php

namespace App\Services;

use App\Models\Bidding;
use App\Models\Entity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiddingApiService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.bidding_api.url');
        $this->apiKey = config('services.bidding_api.key');
    }

    /**
     * Busca licitações na API externa
     *
     * @param array $filters
     * @return array
     */
    public function fetchBiddings(array $filters = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/biddings', $filters);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch biddings from API', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return ['error' => 'Failed to fetch biddings', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('Exception when fetching biddings from API', [
                'message' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Salva licitações da API no banco de dados
     *
     * @param array $apiData
     * @return array
     */
    public function saveBiddingsFromApi(array $apiData)
    {
        $saved = 0;
        $errors = [];

        foreach ($apiData['data'] ?? [] as $biddingData) {
            try {
                // Verificar se a entidade já existe ou criar uma nova
                $entity = Entity::firstOrCreate(
                    ['document' => $biddingData['entity']['document'] ?? null],
                    [
                        'name' => $biddingData['entity']['name'],
                        'type' => $biddingData['entity']['type'] ?? null,
                        'city' => $biddingData['entity']['city'] ?? null,
                        'state' => $biddingData['entity']['state'] ?? null,
                    ]
                );

                // Verificar se a licitação já existe
                $bidding = Bidding::firstOrNew([
                    'reference_number' => $biddingData['reference_number'],
                    'entity_id' => $entity->id
                ]);

                // Preencher ou atualizar os dados da licitação
                $bidding->fill([
                    'title' => $biddingData['title'],
                    'description' => $biddingData['description'] ?? null,
                    'estimated_value' => $biddingData['estimated_value'] ?? null,
                    'notice_link' => $biddingData['notice_link'] ?? null,
                    'status' => $biddingData['status'],
                    'publication_date' => $biddingData['publication_date'] ?? null,
                    'opening_date' => $biddingData['opening_date'] ?? null,
                    'closing_date' => $biddingData['closing_date'] ?? null,
                    'requirements' => $biddingData['requirements'] ?? null,
                    'metadata' => $biddingData['metadata'] ?? null,
                ]);

                $bidding->save();
                $saved++;
            } catch (\Exception $e) {
                Log::error('Error saving bidding from API', [
                    'bidding_data' => $biddingData,
                    'message' => $e->getMessage(),
                ]);
                $errors[] = [
                    'reference' => $biddingData['reference_number'] ?? 'unknown',
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'total' => count($apiData['data'] ?? []),
            'saved' => $saved,
            'errors' => $errors
        ];
    }
}
