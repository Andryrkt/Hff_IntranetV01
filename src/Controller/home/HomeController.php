<?php

namespace App\Controller\home;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home_home")
     *
     * @return Response
     */
    public function home(): Response
    {
        return new Response($this->render('home/home.html.twig'));
    }
}
