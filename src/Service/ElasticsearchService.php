<?php

declare(strict_types=1);

namespace App\Service;

use Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(['http://localhost:9200'])->build();
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
        ]);
    }
}
