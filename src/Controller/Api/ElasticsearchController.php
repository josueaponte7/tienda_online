<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\ElasticsearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ElasticsearchController extends AbstractController
{
    private ElasticsearchService $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }

    #[Route('/api/index-data', name: 'index_data', methods: ['POST'])]
    public function indexData(): JsonResponse
    {
        // Datos de prueba
        $data = [
            'user' => 'Josue Daniel Aponte Segovia',
            'action' => 'login',
            'timestamp' => date('c'),
        ];

        $this->elasticsearchService->index('logs', $data);

        return new JsonResponse(['message' => 'Data indexed successfully']);
    }

    #[Route('/api/search-data', name: 'search_data', methods: ['GET'])]
    public function searchData(): JsonResponse
    {
        $query = [
            'query' => [
                'match' => [
                    'user' => 'Josue Daniel',
                ],
            ],
        ];

        $results = $this->elasticsearchService->search('logs', $query);

        return new JsonResponse($results);
    }
}
