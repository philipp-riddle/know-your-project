<?php

namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    #[Route(path: '/auth/login', name: 'app_auth_login')]
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'navigationRoutes' => ['login', 'signup'],
            'last_username' => $lastUsername,
            'errors' => $error ? [$translator->trans($error->getMessageKey(), domain: 'security')] : [],
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app_auth_login');
    }
}
