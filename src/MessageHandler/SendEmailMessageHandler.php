<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Service\MailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    private LoggerInterface $logger;
    private MailService $mailService;

    public function __construct(LoggerInterface $logger, MailService $mailService)
    {
        $this->logger = $logger;
        $this->mailService = $mailService;
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $this->mailService->sendEmail(
            new SendEmailMessage(
                $message->getEmail(),
                'Bienvenido',
                'Gracias por registrarte.',
            ),
        );
        $this->logger->info(sprintf("INFO: Enviando email a: %s", $message->getEmail()));
    }
}
