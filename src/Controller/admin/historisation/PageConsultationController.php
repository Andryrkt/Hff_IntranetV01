<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class PageConsultationController extends Controller
{
    /**
     * @Route("/admin/consultation-page/dashboard", name="consultation_page_dashboard")
     */
    public function index()
    {

        self::$twig->display(
            'admin/historisation/page-consultation-dashboard.html.twig'
        );
    }
}
