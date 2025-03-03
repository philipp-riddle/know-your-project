<?php

namespace App\Controller\Authentication;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends Controller
{
    #[Route(path: '/auth/login', name: 'app_auth_login')]
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('home'); // user is already logged in
        }

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

    #[Route(path: '/logout', name: 'app_auth_logout')]
    public function logout(): Response
    {
        // no need to implement anything here, Symfony will take care of logging the user out

        return $this->redirectToRoute('app_auth_login');
    }
}
