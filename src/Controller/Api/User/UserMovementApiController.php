<?php

namespace App\Controller\Api\User;

use App\Controller\Api\ApiController;
use App\Entity\Project\Project;
use App\Form\User\UserMovementForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\User\UserMovementService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user/movement')]
class UserMovementApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private UserMovementService $userMovementService,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', name: 'api_userMovement_register', methods: ['POST'])]
    public function registerUserMovement(Request $request)
    {
        $form = $this->createForm(UserMovementForm::class);
        $form = $this->handleFormRequest($form, $request);

        if ($form instanceof JsonResponse) {
            return $form; // return the error response
        }
        
        /** @var Project */
        $project = $form->get('project')->getData();
        $this->checkUserAccess($project);

        $routeName = $form->get('routeName')->getData(); // it is important to know the route name of the user to only reflect the mouse there.
        $mouseRelativeX = $form->get('mouseRelativeX')->getData();
        $mouseRelativeY = $form->get('mouseRelativeY')->getData();
        $hoveredElementDomPath = $form->get('hoveredElementDomPath')->getData();
        $hoveredElementOffsetRelativeX = $form->get('hoveredElementOffsetRelativeX')->getData();
        $hoveredElementOffsetRelativeY = $form->get('hoveredElementOffsetRelativeY')->getData();

        $this->userMovementService->registerMouseMovement(
            $this->getUser(),
            $project,
            $routeName,
            $mouseRelativeX,
            $mouseRelativeY,
            $hoveredElementDomPath,
            $hoveredElementOffsetRelativeX,
            $hoveredElementOffsetRelativeY,
        );

        return $this->createJsonResponse(['success' => true]);
    }
}