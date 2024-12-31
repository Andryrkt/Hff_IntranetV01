<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class PageConsultationController extends Controller
{
    /**
     * @Route("/admin/consultation-page", name="consultation_page_index")
     */
    public function index()
    {
        self::$twig->display(
            'admin/historisation/consultation-page/index.html.twig'
        );
    }
}
