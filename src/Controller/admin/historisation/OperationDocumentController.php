<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class OperationDocumentController extends Controller
{
    /**
     * @Route("/admin/operation-document", name="operation_document_index")
     */
    public function index()
    {
        self::$twig->display(
            'admin/historisation/operation-document/index.html.twig'
        );
    }

    /**
     * @Route("/admin/operation-document/dashboard", name="operation_document_dashboard")
     */
    public function dashboard()
    {
        self::$twig->display(
            'admin/historisation/operation-document/dashboard.html.twig'
        );
    }

    /**
     * @Route("/admin/operation-document/detail", name="operation_document_detail")
     */
    public function detail()
    {
        self::$twig->display(
            'admin/historisation/operation-document/detail.html.twig'
        );
    }
}
