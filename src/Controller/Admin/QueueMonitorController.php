<?php

namespace App\Controller\Admin;

use App\Service\RabbitMQService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QueueMonitorController extends AbstractController
{
    private RabbitMQService $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    #[Route('/admin/queues', name: 'admin_queues')]
    public function index(): Response
    {
        $queues = [
            'email_queue',
            // Añade otras colas si tienes más
        ];

        $queueInfo = [];
        foreach ($queues as $queue) {
            $queueInfo[$queue] = $this->rabbitMQService->getQueueInfo($queue);
        }

        return $this->render('admin/queues/index.html.twig', [
            'queues' => $queueInfo,
        ]);
    }
}
