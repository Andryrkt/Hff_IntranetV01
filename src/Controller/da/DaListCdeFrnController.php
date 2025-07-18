<?php

namespace App\Controller\da;


use App\Entity\da\DaValider;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Model\da\DaListeCdeFrnModel;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Repository\da\DaValiderRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends Controller
{
    private DaValiderRepository $daValiderRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaListeCdeFrnModel $daListeCdeFrnModel;
    private DemandeApproRepository $demandeApproRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daValiderRepository = self::$em->getRepository(DaValider::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
    }

    /**
     * @Route("/da-list-cde-frn", name ="da_list_cde_frn" )
     */
    public function index()
    {
        $this->verifierSessionUtilisateur();

        /** ==== récupération des données à afficher ====*/
        $daValides = $this->donnerAfficher();

        self::$twig->display('da/daListCdeFrn.html.twig', [
            'daValides' => $daValides,
        ]);
    }

    private function donnerAfficher(): array
    {
        /** récupération des ors Zst validé sous forme de tableau */
        $numOrValide = $this->ditOrsSoumisAValidationRepository->findNumOrValide();
        $numOrString = TableauEnStringService::TableauEnString(',', $numOrValide);
        $numOrValideZst = $this->daListeCdeFrnModel->getNumOrValideZst($numOrString);

        /** @var array récupération des lignes de daValider avec version max et or valider */
        $daValiders =  $this->daValiderRepository->getDaOrValider($numOrValideZst);



        return $daValiders;
    }
}
