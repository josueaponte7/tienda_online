<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\LoginStatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginStatisticsController extends AbstractController
{
    public function __construct(private readonly LoginStatisticsService $statisticsService)
    {
    }

    #[Route('/admin/stats/logins', name: 'admin_stats_logins')]
    public function index(): Response
    {
        $loginStats = $this->statisticsService->getLoginCounts();

        return $this->render('admin/stats/logins.html.twig', [
            'loginStats' => $loginStats,
        ]);
    }
}
