<?php

namespace App\Command\Make;

use App\Entity\User\UserInvitation;
use App\Repository\UserInvitationRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\User\UserInvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeUserInvitationCommand extends Command
{
    public function __construct(
        private UserInvitationService $userInvitationService,
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
            ->addOption('email', null, InputOption::VALUE_NONE, 'Whether to send the invitation email.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $sendEmail = $input->getOption('email');

        $userInvitation = $this->userInvitationService->createInvitation($email, null, $name, quiet: !$sendEmail);
        $this->em->persist($userInvitation);
        $this->em->flush();

        $style->success(\sprintf('Invitation sent to "%s".', $email));
        $style->text('Code: ' . $userInvitation->_getCode());

        if ($sendEmail) {
            $style->warning('Email was sent to the user.');
        }

        return Command::SUCCESS;
    }
}