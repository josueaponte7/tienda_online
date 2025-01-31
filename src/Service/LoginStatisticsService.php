<?php

namespace App\Service;

use Elastic\Elasticsearch\Client;

class LoginStatisticsService
{
    private Client $client;

    public function __construct(Client $client, private ElasticsearchService $elasticsearchService)
    {
        $this->client = $client;
    }

    public function getLoginCounts(): array
    {
        $params = [
            'index' => 'login-users',  // Cambia esto si usas otro nombre de índice
            'body' => [
                'size' => 0,
                'aggs' => [
                    'logins_per_user' => [
                        'terms' => ['field' => 'user.keyword'],
                    ],
                ],
                'query' => [
                    'match' => ['action' => 'login']
                ],
            ],
        ];

        $response = $this->client->search($params);

        $logins = [];
        foreach ($response['aggregations']['logins_per_user']['buckets'] as $bucket) {
            $logins[$bucket['key']] = $bucket['doc_count'];
        }

        return $logins;
    }
}
