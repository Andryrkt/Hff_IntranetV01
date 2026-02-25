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
        //recupère les information de l'utilisateur connecter
        $user = $this->getUser();

        //recuperation agence et service autoriser
        $agenceIds = $user->getAgenceAutoriserIds();
        $serviceIds = $user->getServiceAutoriserIds();

        //creation d'autorisation
        $autoriser = $this->autorisationRole();
        $autorisationRoleEnergie = $this->autorisationRoleEnergie();

        //initialisation du champ de recherche
        $ditSearch = $this->initialisationRechercheDit();

        //création et initialisation du formulaire de la recherche
        $form = $this->getFormFactory()->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'autorisationRoleEnergie' => $autorisationRoleEnergie
        ])->getForm();

        $criteria = $this->recupDataFormulaireRecherhce($form, $request);

        //transformer l'objet ditSearch en tableau
        $criteriaTab = $criteria->toArray();

        $this->ajoutCriteredansSession($criteriaTab);

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agenceServiceEmetteur = $this->agenceServiceEmetteur($agenceServiceIps, $autoriser);
        $option = $this->Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds);
        $this->getSessionService()->set('dit_search_option', $option);

        //recupération des donnée
        $paginationData = $this->data($request, $option, $criteria);

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
}
