<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class OperationDocumentController extends Controller
{
    /**
     * @Route("/admin/operation-document/dashboard", name="operation_doc_dashboard")
     */
    public function index()
    {

        self::$twig->display(
            'admin/historisation/operation-document-dashboard.html.twig'
        );
    }
}
