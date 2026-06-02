<?php

namespace App\Controller;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    /**
     * @Route("/secure-file/bap/{numeroDdp}/{fullPath}", name="bap_pdf_viewer")
     */
    public function showBapPdf(string $numeroDdp, string $fullPath): Response
    {
        if (!file_exists($fullPath)) {
            throw new NotFoundHttpException('Le fichier DDP/BAP est introuvable.');
        }

        $response = new BinaryFileResponse($fullPath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, "$numeroDdp.pdf");
        return $response;
    }
}
