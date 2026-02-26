<?php

namespace App\Controller\da;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Form\dit\DitSearchType;
use App\Entity\admin\StatutDemande;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use App\Constants\admin\ApplicationConstant;
use App\Controller\Traits\da\DaListeDitTrait;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\admin\StatutDemandeRepository;
use App\Repository\admin\dit\CategorieAteAppRepository;
use App\Repository\admin\dit\WorTypeDocumentRepository;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;

class DaListeDitController extends Controller
{
    use DaListeDitTrait;

    private DitSearch $ditSearch;
    private DitRepository $ditRepository;
    private DemandeApproRepository $demandeApproRepository;
    private WorTypeDocumentRepository $worTypeDocumentRepository;
    private WorNiveauUrgenceRepository $worNiveauUrgenceRepository;
    private StatutDemandeRepository $statutDemandeRepository;
    private ServiceRepository $serviceRepository;
    private AgenceRepository $agenceRepository;
    private CategorieAteAppRepository $categorieAteAppRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ditSearch = new DitSearch();
        $this->ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->worTypeDocumentRepository = $this->getEntityManager()->getRepository(WorTypeDocument::class);
        $this->worNiveauUrgenceRepository = $this->getEntityManager()->getRepository(WorNiveauUrgence::class);
        $this->statutDemandeRepository = $this->getEntityManager()->getRepository(StatutDemande::class);
        $this->serviceRepository = $this->getEntityManager()->getRepository(Service::class);
        $this->agenceRepository = $this->getEntityManager()->getRepository(Agence::class);
        $this->categorieAteAppRepository = $this->getEntityManager()->getRepository(CategorieAteApp::class);
    }

    /**
     * @Route("/demande-appro/list-dit", name="da_list_dit")
     * 
     * Methode pour afficher et faire une recherche sur la liste DIT
     */
    public function listeDIT(Request $request)
    {
        //initialisation du champ de recherche
        $ditSearch = $this->initialisationRechercheDit();

        // Agences Services autorisés sur le DIT
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DIT);

        //création et initialisation du formulaire de la recherche
        $form = $this->getFormFactory()->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'agenceServiceAutorises' => $agenceServiceAutorises
        ])->getForm();

        $ditSearch = $this->recupDataFormulaireRecherhce($form, $request);

        $this->gererAgenceService($ditSearch, $agenceServiceAutorises);

        //transformer l'objet ditSearch en tableau
        $criteriaTab = $ditSearch->toArray();

        $this->ajoutCriteredansSession($criteriaTab);

        //recupération des donnée
        $paginationData = $this->data($request, $ditSearch, $agenceServiceAutorises);

        return $this->render('da/list-dit.html.twig', [
            'data'            => $paginationData['data'] ?? null,
            'currentPage'     => $paginationData['currentPage'] ?? 0,
            'totalPages'      => $paginationData['lastPage'] ?? 0,
            'criteria'        => $criteriaTab,
            'resultat'        => $paginationData['totalItems'] ?? 0,
            'statusCounts'    => $paginationData['statusCounts'] ?? 0,
            'form'            => $form->createView(),
            'formIsSubmitted' => $form->isSubmitted(),
        ]);
    }

    private function gererAgenceService(DitSearch $ditSearch, array $agenceServiceAutorises): void
    {
        // Changer le serviceEmetteur
        if ($ditSearch->getServiceEmetteur()) {
            $ligneId = $ditSearch->getServiceEmetteur();
            if ($ligneId && isset($agenceServiceAutorises[$ligneId])) {
                $ditSearch->setServiceEmetteur($agenceServiceAutorises[$ligneId]['service_id']);
            }
        }

        // Changer le serviceDebiteur
        if ($ditSearch->getServiceDebiteur()) {
            $ligneId = $ditSearch->getServiceDebiteur();
            if ($ligneId && isset($agenceServiceAutorises[$ligneId])) {
                $ditSearch->setServiceDebiteur($agenceServiceAutorises[$ligneId]['service_id']);
            }
        }
    }
}
