<?php

namespace App\Api\da;

use App\Controller\Controller;
use App\Entity\dw\DwBcAppro;
use App\Repository\dw\DwBcApproRepository;
use Symfony\Component\Routing\Annotation\Route;

class DaTelechargeBcValiderApi extends Controller
{
    private DwBcApproRepository $dwBcApproRepository;

    public function __construct()
    {
        parent::__construct();

        $this->dwBcApproRepository = self::$em->getRepository(DwBcAppro::class);
    }

    /**
     * @Route("/api/generer-bc-valider/{numBc}", name="da_telecharge_bc_valider", methods={"GET"})
     */
    public function telechargeBcValider(string $numBc)
    {
        $path = $this->dwBcApproRepository->getPath($numBc);

        $filePath = "C:\\wamp64\\www\\Upload\\" . $path;
        // En-têtes pour forcer le téléchargement
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="bon_commande_' . $numBc . '.pdf"');
        header('Content-Length: ' . filesize($filePath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // Envoi du fichier
        readfile($filePath);
        exit;
    }
}
