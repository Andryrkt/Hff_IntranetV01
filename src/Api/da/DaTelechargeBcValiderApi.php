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

        $this->dwBcApproRepository = $this->getEntityManager()->getRepository(DwBcAppro::class);
    }

    /**
     * @Route("/api/generer-bc-valider/{numBc}", name="api_da_telecharge_bc_valider", methods={"GET"})
     */
    public function telechargeBcValider(string $numBc)
    {
        $path = $this->dwBcApproRepository->getPath($numBc);
        if ($path) {
            $filePath = $_ENV['BASE_PATH_FICHIER'] . DIRECTORY_SEPARATOR . $path;
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
        } else {
            $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => 'Le BC n\'est pas encore disponible.']);
        }
    }
}
