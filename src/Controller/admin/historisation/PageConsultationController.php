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

    /**
     * @Route("/admin/consultation-page/dashboard", name="consultation_page_dashboard")
     */
    public function dashboard()
    {
        self::$twig->display(
            'admin/historisation/consultation-page/dashboard.html.twig'
        );
    }

    /**
     * @Route("/admin/consultation-page/detail", name="consultation_page_detail")
     */
    public function detail()
    {
        self::$twig->display(
            'admin/historisation/consultation-page/detail.html.twig'
        );
    }
}
