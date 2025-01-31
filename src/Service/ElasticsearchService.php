<?php

declare(strict_types=1);

namespace App\Service;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function index(string $index, array $data): void
    {
        $this->client->index([
            'index' => $index,
            'body' => $data,
        ]);
    }

    public function search(string $index, array $query): array
    {
        return $this->client->search([
            'index' => $index,
            'body' => $query,
        ])->asArray(); // Convierte la respuesta en un array
    }
}
