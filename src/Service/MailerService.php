<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
    ) { }

    public function sendMail(string $recipient, string $subject, string $body)
    {
        $email = (new Email())
            ->from('riddle-mailer@philippmartini.de')
            ->to($recipient)
            ->subject($subject)
            ->html($body)
            ->text(\strip_tags($body));

        $this->mailer->send($email);
    }
}