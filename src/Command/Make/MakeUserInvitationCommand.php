<?php

namespace App\Command\Make;

use App\Entity\User\UserInvitation;
use App\Repository\UserInvitationRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeUserInvitationCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private UserInvitationRepository $userInvitationRepository,
        private EntityManagerInterface $em,
        private MailerService $mailerService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('make:user-invitation')
            ->setDescription('Creates a new user invitation; used for sending out beta invites currently.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email address of the user to invite.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the user to invite; personalises the experience.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');

        if (!\filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $style->error('Invalid email address.');

            return Command::FAILURE;
        }

        if (null !== $this->userRepository->findOneBy(['email' => $email])) {
            $style->error('User with this email already exists.');

            return Command::FAILURE;
        }

        if (null !== $userInvitation = $this->userInvitationRepository->findOneBy(['email' => $email])) {
            $style->error(\sprintf('"%s" already has an invitation.', $userInvitation->getName()));

            return Command::FAILURE;
        }

        $userInvitation = (new UserInvitation())
            ->setEmail($email)
            ->setName($name)
            ->setCode(\bin2hex(\random_bytes(16)))
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        $this->mailerService->sendUserInvitationToNewEmail($userInvitation);
        $this->em->persist($userInvitation);
        $this->em->flush();

        $style->success(\sprintf('Invitation sent to "%s".', $email));

        return Command::SUCCESS;
    }
}