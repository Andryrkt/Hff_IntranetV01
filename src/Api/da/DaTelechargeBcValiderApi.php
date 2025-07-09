<?php

namespace App\Api\da;

use App\Entity\dw\DwBcClient;
use App\Controller\Controller;
use App\Repository\dw\DwBcClientRepository;
use Symfony\Component\Routing\Annotation\Route;

class DaTelechargeBcValiderApi extends Controller
{
    private DwBcClientRepository $dwBcClientRepository;
    public function __construct()
    {
        parent::__construct();

        $this->dwBcClientRepository = self::$em->getRepository(DwBcClient::class);
    }

    /**
     * @Route("/api/generer-bc-valider/{numBc}", name="da_telecharge_bc_valider", methods={"GET"})
     */
    public function telechargeBcValider(string $numBc)
    {
        $path = $this->dwBcClientRepository->getPath($numBc);

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
