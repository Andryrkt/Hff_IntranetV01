<?php

namespace App\Controller\da\DossierDa;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Service\da\DocRattacheService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDossierDaController extends Controller
{
    private DocRattacheService $docRattacheService;

    public function __construct(DocRattacheService $docRattacheService)
    {
        $this->docRattacheService = $docRattacheService;
    }

    /**
     * @Route("/dossier-da/{numDa}", name="da_dossier_da")
     */
    public function dossierDa($numDa)
    {

        $demandeAppro = $this->getEntityManager()->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $numDa]);

        $fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);

        return $this->render("da/dossier-da.html.twig", [
            'fichiers' => $fichiers,
        ]);
    }
}
