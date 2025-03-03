<?php

namespace App\Service;

use App\Entity\User\UserInvitation;
use App\Exception\PreconditionFailedException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
    ) { }

    public function sendMail(string $recipient, string $subject, string $body): void
    {
        $email = (new Email())
            ->from(new Address('know-your-project@philippmartini.de', 'Know Your Project'))
            ->replyTo(new Address('mail@philippmartini.de', 'Philipp Martini'))
            ->to($recipient)
            ->subject($subject)
            ->html($body)
            ->text(\strip_tags($body));

        $this->mailer->send($email);
    }

    // ========= APPLIED sendMail methods; centralized here to have all mails at once place

    public function sendUserInvitationToNewEmail(UserInvitation $userInvitation): void
    {
        $this->sendMail(
            $userInvitation->getEmail(),
            'You were invited to Know Your Project',
            \sprintf('
                Hi %s!
                <br>
                You were invited to join the beta of <strong>Know Your Project</strong>.
                <br>
                <br>
                Know Your Project combines Kanban boards with powerful knowledge management features, such as an extensive page editor, full-text semantic search, and more.
                It works best with your company\'s projects and other colleagues to collaborate with, but you can also use it just as good for your personal projects.
                <br>
                <br>
                Please click on the following link to sign up: <a href="%s">Sign up</a>
                <br>
                <br>
                PS: If you have any questions or feedback, feel free to reply to this email at any time.
                <br>
                <br>
                Thanks for participating in the beta, <br>
                <i>Philipp</i>
            ', $userInvitation->getName() ?? 'there', $this->getPublicUrl(\sprintf('auth/verify/%s', $userInvitation->_getCode()))),
        );
    }

    public function sendUserInvitationToExistingUser(UserInvitation $userInvitation): void
    {
        if (null === $user = $userInvitation->getUser()) {
            throw new PreconditionFailedException('User invitation has no user set');
        }

        $this->sendMail(
            $user->getEmail(),
            'You were invited to a new project (Know Your Project)',
            \sprintf('
                Hi there!
                <br>
                You were invited to join a new project.
                <br>
                <br>
                Please login to accept the invitation: <a href="%s">Login</a>
            ', $this->getPublicUrl('auth/login')),
        );
    }

    private function getPublicUrl(string $path): string
    {
        $symfonyPublicUrl = \trim($_ENV['SYMFONY_PUBLIC_URL'] ?? '');

        if ('' === $symfonyPublicUrl) {
            throw new PreconditionFailedException('SYMFONY_PUBLIC_URL is not set');
        }

        return \rtrim($symfonyPublicUrl, '/').'/'.$path;
    }
}