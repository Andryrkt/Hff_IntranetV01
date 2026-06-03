<?php

namespace App\Controller;

use App\Controller\Controller;
use App\Model\ddp\DemandePaiementModel;
use App\Service\da\FileCheckerService;
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
        $tableMap = ['DPR' => 'DW_regularisation_ddp', 'BAP' => 'DW_bon_a_payer'];
        $columnNameMap = ['DPR' => 'numero_ddr', 'BAP' => 'numero_bap'];

        $demandePaiementModel = new DemandePaiementModel();
        $fullPath = $demandePaiementModel->getFilePathDdp($numeroDdp, $tableMap[$code] ?? 'DW_demande_de_paiement', $columnNameMap[$code] ?? 'numero_ddp'); // par défaut c'est "DW_demande_de_paiement", "numero_ddp"

        if ($fullPath) {
            $fullPath = $_ENV['BASE_PATH_FICHIER'] . '/' . $fullPath;
            if ((new FileCheckerService())->checkFileExists($fullPath)) {
            }
        } else {
            throw new NotFoundHttpException('Le fichier DDP/BAP est introuvable.');
        }

        $response = new BinaryFileResponse($fullPath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, "$numeroDdp.pdf");
        return $response;
    }
}
