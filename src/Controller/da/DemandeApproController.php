<?php

namespace App\Controller\da;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DemandeApproController extends Controller
{
    /**
     * @Route("/first-form", name="da_first_form")
     */
    public function firstForm()
    {
        self::$twig->display('da/first-form.html.twig');
    }

    /**
     * @Route("/list-dit", name="da_list_dit")
     */
    public function listeDIT()
    {
        self::$twig->display();
    }

    /**
     * @Route("/new/{id}", name="da_new")
     */
    public function new($id)
    {
        self::$twig->display();
    }
}
