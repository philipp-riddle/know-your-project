<?php

namespace App\Controller\Api\User;

use App\Controller\Api\CrudApiController;
use App\Entity\User\User;
use App\Service\File\Uploader;
use App\Service\Helper\ApiControllerHelperService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private Uploader $uploader,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        return $this->crudGet($this->getUser());
    }

    #[Route('/profile-picture', name: 'api_user_profile_picture_upload', methods: ['POST'])]
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        // we have to check if the files are uploaded properly first; get all of the files from the request
        $files = $request->files->all();

        if (\array_keys($files) !== ['picture'] || null === $pictureFile = @$files['picture']) {
            throw new BadRequestException('Invalid user profile picture upload; please upload one valid file into the "picture" field.');
        }

        // save the profile picture to a File entity; attach it to the user in the next step.
        $file = $this->uploader->upload(
            $this->getUser(),
            project: null,
            uploadedFile: $pictureFile,
        );

        $currentUser = $this->getUser();
        $currentUser->setProfilePicture($file);
        $this->em->flush();

        return $this->jsonSerialize($currentUser); // simply return the user entity with the updated profile picture
    }

    public function getEntityClass(): string
    {
        return User::class;
    }

    public function getFormClass(): string
    {
        throw new \RuntimeException('Form not implemented for User entity.');
    }
}