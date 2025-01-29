<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'title' => 'Panel de Administración',
        ]);
    }

    #[Route('/admin/queue-status', name: 'admin_queue_status')]
    public function queueStatus(): Response
    {
        // Simulación de datos para RabbitMQ
        $queueStatus = [
            'queueName' => 'email_queue',
            'messagesReady' => 10,
            'messagesProcessed' => 50,
        ];

        return $this->render('admin/queue_status.html.twig', [
            'queueStatus' => $queueStatus,
        ]);
    }

    #[Route('/admin/activity-stats', name: 'admin_activity_stats')]
    public function activityStats(): Response
    {
        // Aquí puedes consultar Elasticsearch o Redis para estadísticas de actividad
        $activityStats = [
            ['user' => 'admin@example.com', 'logins' => 5],
            ['user' => 'user@example.com', 'logins' => 3],
        ];

        return $this->render('admin/activity_stats.html.twig', [
            'activityStats' => $activityStats,
        ]);
    }
}
