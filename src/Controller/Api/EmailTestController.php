<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Message\SendEmailMessage;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;

/**
 * Controlador para probar el envío de correos electrónicos.
 *
 * Este controlador utiliza Symfony Messenger para manejar mensajes
 * relacionados con el envío de correos electrónicos.
 */
class EmailTestController extends AbstractController
{
    private MailService $mailService;
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    #[Route('/api/test-email', name: 'test_email', methods: ['GET'])]
    public function testEmail(MessageBusInterface $bus): JsonResponse
    {

        $this->bus->dispatch(
            new SendEmailMessage(
                'josue@example.com',
                'Hola, Bienvenido',
                'Gracias por registrarte Josue Aponte.',
            ),
        );
        return new JsonResponse(['message' => 'Correo enviado correctamente']);
    }
}
