<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Message\SendEmailMessage;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Controlador para probar el envío de correos electrónicos.
 *
 * Este controlador utiliza Symfony Messenger para manejar mensajes
 * relacionados con el envío de correos electrónicos.
 */

class EmailTestController extends AbstractController
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    #[Route('/api/test-email', name: 'test_email', methods: ['GET'])]


    public function testEmail(MessageBusInterface $bus): JsonResponse
    {
        $bus->dispatch(new SendEmailMessage('test@example.com'));
        return new JsonResponse(['message' => 'Correo enviado correctamente']);
    }
}
