<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Helper\DefaultNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private DefaultNormalizer $normalizer,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    /**
     * This is where the Vue.js page renders.
     */
    #[Route('/', name: 'home')]
    public function home(Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();

        // if the user has no selected project, we only need to pass the user data to the frontend.
        // in this case the user is also in the setup step which means that no entity other than the user is needed.
        if (null === $user->getSelectedProject()) {
            $templateData = [
                'user' => $this->normalizer->normalize($this->getUser(), $user),
                'project' => null,
                'mercureConfig' => null,
            ];
        } else {
            $templateData = [
                // inject normalised data into the frontend to save API calls to fetch ...
                // - selected project.
                // - current user.
                // this makes the bootup of the frontend faster as it reduces the number of API calls on page load.
                'user' => $this->normalizer->normalize($this->getUser(), $user),
                'project' => $this->normalizer->normalize($this->getUser(), $user->getSelectedProject()),
    
                // give the frontend context for our Mercure event integration;
                // this way changing the config in one place will update the frontend as well.
                'mercureConfig' => [
                    'url' => $_ENV['MERCURE_PUBLIC_URL'],
                    'jws' => $this->mercureIntegration->createJWS($request, $user->getSelectedProject()),
                    'topics' => $this->mercureIntegration->getDefaultTopicsToSubscribe($user->getSelectedProject()),
                ],
            ];
        }

        return $this->render('index.html.twig', $templateData);
    }
}