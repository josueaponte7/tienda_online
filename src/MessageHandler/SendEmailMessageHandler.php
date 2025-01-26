<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

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
            $message->getEmail(),
            'De nuevo AJAAA',
            '<h1>¡EPALE!</h1><p>AQUII.</p>'
        );
        $this->logger->info(sprintf("INFO: Enviando email a: %s", $message->getEmail()));
    }
}
