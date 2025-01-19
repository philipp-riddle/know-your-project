<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Helper\DefaultNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        private DefaultNormalizer $normalizer,
    ) { }

    /**
     * This is where the Vue.js page renders.
     */
    #[Route('/', name: 'home')]
    public function home()
    {
        /** @var User */
        $user = $this->getUser();

        $response = $this->render('index.html.twig', [
            // inject some data into the frontend already to save API calls to fetch the selected project and current user information.
            'user' => $this->normalizer->normalize($this->getUser(), $user),
            'project' => $this->normalizer->normalize($this->getUser(), $user->getSelectedProject()),
        ]);
        // @todo: Uncomment the following line to enable Content Security Policy
        // $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self'; img-src 'self'; frame-src 'self';");

        return $response;
    }
}