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
}
