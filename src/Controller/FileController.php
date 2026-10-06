<?php

namespace App\Controller;

use App\Controller\Controller;
use App\Service\ddp\DemandePaiementFileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    /**
     * @Route("/secure-file/bap/{numeroDdp}/{code}", name="bap_pdf_viewer")
     */
    public function showBapPdf(string $numeroDdp, string $code): Response
    {
        $fileInfo = (new DemandePaiementFileService())->getFileInfo($numeroDdp, $code);

        if (!$fileInfo['exists']) throw new NotFoundHttpException('Le fichier DDP/BAP est introuvable.');

        $response = new BinaryFileResponse($fileInfo['path']);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, "$numeroDdp.pdf");
        return $response;
    }
}
