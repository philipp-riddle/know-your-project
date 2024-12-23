<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * This is where the Vue.js page renders.
     */
    #[Route('/', name: 'home')]
    public function home()
    {
        $response = $this->render('index.html.twig');
        // @todo: Uncomment the following line to enable Content Security Policy
        // $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self'; img-src 'self'; frame-src 'self';");

        return $response;
    }
}