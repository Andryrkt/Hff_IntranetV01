<?php

namespace App\Controller;

use Twig\Environment;
use App\Form\MyFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MyController
{
    private $twig;
    private $formFactory;
    private $urlGenerator;

    public function __construct(Environment $twig, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator)
    {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function index(Request $request): Response
    {
        // Utilisation des services injectés
        $form = $this->formFactory->create(MyFormType::class);
        return new Response($this->twig->render('index.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
