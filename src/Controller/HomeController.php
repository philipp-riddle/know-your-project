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
        return $this->render('index.html.twig');
    }
}