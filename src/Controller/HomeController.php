<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Service\Helper\DefaultNormalizer;
use App\Service\Integration\MercureIntegration;
use App\Service\PageService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function __construct(
        private DefaultNormalizer $normalizer,
        private PageService $pageService,
        private MercureIntegration $mercureIntegration,
    ) { }

    /**
     * This is where the Vue.js page renders.
     */
    #[Route('/', name: 'home')]
    public function home(Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();

        $response = $this->render('index.html.twig', [
            // inject some data into the frontend already to save API calls to fetch ...
            // - selected project 
            // - current user information.
            // - untagged pages / notes
            'user' => $this->normalizer->normalize($this->getUser(), $user),
            'project' => $this->normalizer->normalize($this->getUser(), $user->getSelectedProject()),
            'untaggedPages' => $this->normalizer->normalize($user, $this->pageService->getUntaggedPages($user->getSelectedProject())),

            // give the frontend context for our Mercure event integration;
            // this way changing the config in one place will update the frontend as well.
            'mercureConfig' => [
                'url' => $_ENV['MERCURE_PUBLIC_URL'],
                'jws' => $this->mercureIntegration->createJWS($request, $user->getSelectedProject()),
                'topics' => $this->mercureIntegration->getDefaultTopicsToSubscribe($user->getSelectedProject()),
            ],
        ]);
        // @todo: Uncomment the following line to enable Content Security Policy
        // $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self'; img-src 'self'; frame-src 'self';");

        return $response;
    }
}