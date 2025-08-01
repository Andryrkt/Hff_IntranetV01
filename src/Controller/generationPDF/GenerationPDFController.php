<?php

namespace App\Controller\generationPDF;

use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Entity\admin\utilisateur\Role;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\dit\DitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route(path="/api/generation-PDF") */
class GenerationPDFController extends Controller
{
    use DaTrait;

    private DitRepository $ditRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route(path="/da/{numeroDemandeAppro}/{numeroVersionMax}", name="generation_pdf_da")
     */
    public function genererPdfDa(string $numeroDemandeAppro, int $numeroVersionMax)
    {
        if (!in_array(Role::ROLE_ADMINISTRATEUR, $this->getUser()->getRoleIds())) {
            $this->redirectToRoute('security_signin');
        }
        $this->creationPdf($numeroDemandeAppro, $numeroVersionMax);
    }

    /**
     * @Route(path="/da-direct/{numeroDemandeAppro}/{numeroVersionMax}", name="generation_pdf_da_direct")
     */
    public function genererPdfDaDirect(string $numeroDemandeAppro, int $numeroVersionMax)
    {
        $demandeAppro = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numeroDemandeAppro]);
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numeroDemandeAppro, 'numeroVersion' => $numeroVersionMax]);

        if (!in_array(Role::ROLE_ADMINISTRATEUR, $this->getUser()->getRoleIds())) {
            $this->redirectToRoute('security_signin');
        }

        $this->creationPdfSansDitAvaliderDW($demandeAppro, $dals);
    }
}
