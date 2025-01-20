<?php

namespace App\Controller\Api\User;

use App\Controller\Api\CrudApiController;
use App\Entity\Project;
use App\Entity\UserInvitation;
use App\Form\User\UserInvitationForm;
use App\Repository\UserInvitationRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Helper\TestEnvironment;
use App\Service\MailerService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user/invitation')]
class UserInvitationApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private UserService $userService,
        private MailerService $mailerService,
        private UserInvitationRepository $userInvitationRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', name: 'api_user_invitation_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (UserInvitation $userInvitation) {
                if ($this->userRepository->findOneBy(['email' => $userInvitation->getEmail()])) {
                    throw new BadRequestException('User with this email already exists');
                }

                if ($this->userInvitationRepository->findOneBy(['email' => $userInvitation->getEmail()])) {
                    throw new BadRequestException('User with this email was already invited');
                }

                $userInvitation->setCode(bin2hex(openssl_random_pseudo_bytes(10))); // attach random code to user invitation so that it can be used to verify the user

                // do not send email in test environment
                if (TestEnvironment::isActive()) {
                    $this->mailerService->sendMail(
                        $userInvitation->getEmail(),
                        'You were invited to Know Your Project',
                        'Hi there! <br>You were invited to join Know Your Project. <br><br>Please click on the following link to register: <a href="http://127.0.0.1:8080/auth/verify/'.$userInvitation->_getCode().'">Register</a>'
                    );
                }

                return $userInvitation;
            },
        );
    }

    #[Route('/{userInvitation}', name: 'api_user_invitation_delete', methods: ['DELETE'])]
    public function delete(UserInvitation $userInvitation): JsonResponse
    {
        return $this->crudDelete($userInvitation);
    }

    #[Route('/project/list/{project}', name: 'api_user_invitation_project_list', methods: ['GET'])]
    public function projectInvitationList(Project $project)
    {
        return $this->crudList(['project' => $project]);
    }

    public function getEntityClass(): string
    {
        return UserInvitation::class;
    }

    public function getFormClass(): string
    {
        return UserInvitationForm::class;
    }
}