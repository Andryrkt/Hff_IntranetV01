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
use App\Service\da\DaListeDitService;
use App\Dto\Dit\DitListItemDto;
use App\Dto\Dit\DitStatusCountDto;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\admin\StatutDemandeRepository;
use App\Repository\admin\dit\CategorieAteAppRepository;
use App\Repository\admin\dit\WorTypeDocumentRepository;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use App\Service\Admin\UrlIdCipher;
use App\Service\security\SecurityService;

class DaListeDitController extends Controller
{
    private DaListeDitService $daListeDitService;
    private DitSearch $ditSearch;
    private DemandeApproRepository $demandeApproRepository;
    private WorTypeDocumentRepository $worTypeDocumentRepository;
    private WorNiveauUrgenceRepository $worNiveauUrgenceRepository;
    private StatutDemandeRepository $statutDemandeRepository;
    private ServiceRepository $serviceRepository;
    private AgenceRepository $agenceRepository;
    private CategorieAteAppRepository $categorieAteAppRepository;

    public function __construct(DaListeDitService $daListeDitService)
    {
        $this->daListeDitService = $daListeDitService;
        $this->ditSearch = new DitSearch();
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
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        // Vérifier la permission de voir tous les données
        $multisuccursale = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);

        //initialisation du champ de recherche
        $ditSearch = $this->initialisationRechercheDit();

        // Agences Services autorisés sur le DIT
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DIT);
        $allAgenceServices = $this->getSecurityService()->getAllAgenceServices();

        //création et initialisation du formulaire de la recherche
        $form = $this->getFormFactory()->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'allAgenceServices' => $allAgenceServices
        ])->getForm();

        $ditSearch = $this->recupDataFormulaireRecherhce($form, $request);

        $this->gererAgenceService($ditSearch, $allAgenceServices);

        //transformer l'objet ditSearch en tableau
        $criteriaTab = $ditSearch->toArray();

        $this->ajoutCriteredansSession($criteriaTab);

        // Agence et service par défaut
        $agenceIdUser = $this->getSecurityService()->getAgenceIdUser();
        $serviceIdUser = $this->getSecurityService()->getServiceIdUser();
        $codeAgenceUser = $this->getSecurityService()->getCodeAgenceUser();

        // Vérifier le permission de voir liste avec débiteur sur la page courante
        $peutVoirListeAvecDebiteur = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_AUTH_2);

        //recupération des donnée
        $paginationData = $this->daListeDitService->data($request->query->getInt('page', 1), $ditSearch, $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $codeAgenceUser, $peutVoirListeAvecDebiteur, $codeSociete, $multisuccursale);

        return $this->render('da/list-dit.html.twig', [
            'data'            => array_map(fn($item) => DitListItemDto::fromEntity($item, $this->getUrlGenerator(), new UrlIdCipher), $paginationData['data'] ?? []),
            'currentPage'     => $paginationData['currentPage'] ?? 0,
            'totalPages'      => $paginationData['lastPage'] ?? 0,
            'criteria'        => $criteriaTab,
            'resultat'        => $paginationData['totalItems'] ?? 0,
            'statusCounts'    => array_map([DitStatusCountDto::class, 'fromRow'], $paginationData['statusCounts'] ?? []),
            'form'            => $form->createView(),
            'formIsSubmitted' => $form->isSubmitted(),
        ]);
    }

    /**
     * Methode pour l'initialisation des donners dans les champs de formulaire
     */
    private function initialisationRechercheDit(): DitSearch
    {
        $criteria = $this->getSessionService()->get('list_dit_da_search_criteria');
        if (!empty($criteria)) {
            $typeDocument = $criteria['typeDocument'] === null ? null : $this->worTypeDocumentRepository->find($criteria['typeDocument']->getId());
            $niveauUrgence = $criteria['niveauUrgence'] === null ? null : $this->worNiveauUrgenceRepository->find($criteria['niveauUrgence']->getId());
            $statut = $criteria['statut'] === null ? null : $this->statutDemandeRepository->find($criteria['statut']->getId());
            $categorie = $criteria['categorie'] === null ? null : $this->categorieAteAppRepository->find($criteria['categorie']);
        } else {
            $typeDocument = null;
            $niveauUrgence = null;
            $statut = null;
            $categorie = null;
        }

        $this->ditSearch
            ->setStatut($statut)
            ->setNiveauUrgence($niveauUrgence)
            ->setTypeDocument($typeDocument)
            ->setInternetExterne('INTERNE')
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setIdMateriel($criteria['idMateriel'] ?? null)
            ->setNumParc($criteria['numParc'] ?? null)
            ->setNumSerie($criteria['numSerie'] ?? null)
            ->setAgenceEmetteur($criteria['agenceEmetteur'] ?? null)
            ->setServiceEmetteur($criteria['serviceEmetteur'] ?? null)
            ->setAgenceDebiteur($criteria['agenceDebiteur'] ?? null)
            ->setServiceDebiteur($criteria['serviceDebiteur'] ?? null)
            ->setNumDit($criteria['numDit'] ?? null)
            ->setNumOr($criteria['numOr'] ?? null)
            ->setStatutOr($criteria['statutOr'] ?? null)
            ->setDitSansOr($criteria['ditSansOr'] ?? null)
            ->setCategorie($categorie)
            ->setUtilisateur($criteria['utilisateur'] ?? null)
            ->setSectionAffectee($criteria['sectionAffectee'] ?? null)
            ->setSectionSupport1($criteria['sectionSupport1'] ?? null)
            ->setSectionSupport2($criteria['sectionSupport2'] ?? null)
            ->setSectionSupport3($criteria['sectionSupport3'] ?? null)
            ->setEtatFacture($criteria['etatFacture'] ?? null)
        ;

        return $this->ditSearch;
    }

    /**
     * Ajouter les information de la recherche dans la session
     */
    private function ajoutCriteredansSession(array $criteriaTab): void
    {
        $this->getSessionService()->set('list_dit_da_search_criteria', $criteriaTab);
    }

    private function recupDataFormulaireRecherhce($form, Request $request): DitSearch
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->ditSearch = $form->getData();
        }
        return $this->ditSearch;
    }

    private function gererAgenceService(DitSearch $ditSearch, array $allAgenceServices): void
    {
        // Changer le serviceEmetteur
        if ($ditSearch->getServiceEmetteur()) {
            $ligneId = $ditSearch->getServiceEmetteur();
            if ($ligneId && isset($allAgenceServices[$ligneId])) {
                $ditSearch->setServiceEmetteur($allAgenceServices[$ligneId]['service_id']);
            }
        }

        // Changer le serviceDebiteur
        if ($ditSearch->getServiceDebiteur()) {
            $ligneId = $ditSearch->getServiceDebiteur();
            if ($ligneId && isset($allAgenceServices[$ligneId])) {
                $ditSearch->setServiceDebiteur($allAgenceServices[$ligneId]['service_id']);
            }
        }
    }
}
