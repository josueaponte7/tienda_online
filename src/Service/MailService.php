<?php

declare(strict_types=1);

namespace App\Service;

use App\Message\SendEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(SendEmailMessage $message): void
    {
        $email = (new Email())
            ->from('no-reply@tienda-online.local')
            ->to($message->getEmail())
            ->subject($message->getSubject())
            ->text($message->getContent());

        $this->mailer->send($email);
    }
}
