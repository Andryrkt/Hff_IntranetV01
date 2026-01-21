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
     * @Route("/secure-file/bap/{numeroDa}/{urlPdf}", name="bap_pdf_viewer")
     */
    public function showBapPdf(string $numeroDa, string $urlPdf): Response
    {
        // Get projectDir from the container via the kernel service
        $projectDir = $_ENV['BASE_PATH_FICHIER'];

        $relativePath = "/da/$numeroDa/$urlPdf";
        $fullPath = $projectDir . $relativePath;

        if (!file_exists($fullPath)) {
            throw new NotFoundHttpException('Le fichier BAP est introuvable.');
        }

        $response = new BinaryFileResponse($fullPath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $urlPdf
        );
        return $response;
    }
}
