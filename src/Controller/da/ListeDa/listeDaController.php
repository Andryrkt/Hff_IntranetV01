<?php

namespace App\Controller\da\ListeDa;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaTrait;
use App\Entity\admin\Agence;
use App\Entity\admin\Application;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSearch;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use App\Form\da\DaSearchType;
use App\Mapper\Da\DaAfficherMapper;
use App\Repository\admin\AgenceRepository;
use App\Repository\da\DaAfficherRepository;
use App\Service\da\PermissionDaService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use AutorisationTrait;
    use DaTrait;

    private DaAfficherRepository $daAfficherRepository;
    private AgenceRepository $agenceRepository;
    private DaAfficherMapper $daAfficherMapper;
    private PermissionDaService $permissionDaService;

    public function __construct()
    {
        parent::__construct();
        $em = $this->getEntityManager();
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->agenceRepository = $em->getRepository(Agence::class);
        $this->daAfficherMapper = new DaAfficherMapper($this->getUrlGenerator());
        $this->permissionDaService = new PermissionDaService();

        $this->initDaTrait();
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);

        /** Initialisation Recherche */
        $daSearch = new DaSearch;
        $this->initialisationRechercheDa($daSearch);

        // Formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, [
            'method' => 'GET',
            'estAppro' => $this->estUserDansServiceAppro(),
            'codeAgence'  => $this->getUser()->getCodeAgenceUser(),
            'codeService' => $this->getUser()->getCodeServiceUser(),
        ])->getForm();
        $criteria = $this->traitementFormualireRecherche($request, $form, $daSearch);

        // Visuel de tri
        $sortJoursClass = false;
        if (!empty($criteria['sortNbJours'])) {
            $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';
        }

        // Pagination (Réduction de la limite de 500 à 20 pour la fluidité)
        $page = $request->query->getInt('page', 1);
        $limit = 100;

        // Récupération des données
        $user = $this->getUser();
        $idAgenceUser = $this->agenceRepository->findIdByCodeAgence($user->getCodeAgenceUser());

        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA(
            $user,
            $criteria,
            $idAgenceUser,
            $this->estUserDansServiceAppro(),
            $this->estUserDansServiceAtelier(),
            $this->estAdmin(),
            $page,
            $limit
        );

        // Préparation des données pour la vue (Via Presenter avec Cache)
        $dataPrepared = $this->daAfficherMapper->mapList($paginationData['data'], [
            'estAdmin'   => $this->estAdmin(),
            'estAppro'   => $this->estUserDansServiceAppro(),
            'estAtelier' => $this->estUserDansServiceAtelier(),
            'estCreateur' => $this->estCreateurDeDADirecte()
        ]);

        // Détection code centrale
        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $codeCentraleVisible = $this->estAdmin() || in_array($agenceServiceIps['agenceIps']->getCodeAgence(), ['90', '91', '92']);

        // Formulaire Date Livraison
        $formDateLivraison = $this->getFormFactory()->createBuilder(DaModalDateLivraisonType::class)->getForm();
        $this->TraitementFormulaireDateLivraison($request, $formDateLivraison);

        return $this->render('da/list-da.html.twig', [
            'data'              => $dataPrepared,
            'form'              => $form->createView(),
            'criteria'          => $criteria,
            'codeCentrale'      => $codeCentraleVisible,
            'sortJoursClass'    => $sortJoursClass,
            'currentPage'       => $paginationData['currentPage'],
            'totalPages'        => $paginationData['lastPage'],
            'resultat'          => $paginationData['totalItems'],
            'formDateLivraison' => $formDateLivraison->createView(),
            'mesDaActif'        => $request->query->get('mes_da_a_traiter') == 1,
        ]);
    }

    private function traitementFormualireRecherche(Request $request, FormInterface $form, DaSearch $daSearch)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $criteria = $daSearch->toArray();

            // On vérifie si d'autres critères de recherche sont remplis (hors 'afficherDaTraiter')
            $hasOtherCriteria = false;
            foreach ($criteria as $key => $value) {
                if ($key !== 'afficherDaTraiter' && $value !== null && $value !== false && $value !== '') {
                    $hasOtherCriteria = true;
                    break;
                }
            }

            if ($criteria['afficherDaTraiter']) {
                $criteria = $this->statutDatraiter($criteria);
            }

            // Sauvegarde classique des critères issus du formulaire
            $this->getSessionService()->set('criteria_search_list_da', $criteria);
        }
        // Gestion spécifique "Mes DA à traiter"
        else {
            $criteria = $daSearch->toArray();
            $criteria = $this->statutDatraiter($criteria);

            $this->getSessionService()->set('criteria_search_list_da_80_app', $criteria);
        }



        return $criteria;
    }

    private function statutDatraiter(array $criteria): array
    {
        $user = $this->getUser();
        $codeAgenceUser = $user->getCodeAgenceUser();
        $codeServiceUser = $user->getCodeServiceUser();

        // On ne garde que la persistance du flag et les filtres imposés
        // $criteria = [];

        if ($codeAgenceUser == '80' && $codeServiceUser == 'APP') {
            if ($criteria['afficherCloturees']) {
                $criteria['statutDA'] = StatutDaConstant::TRAITER_APPRO_LIST_CLOTURE;
                $criteria['statutBC'] = StatutBcConstant::TRAITER_APPRO_LIST;
            } else {
                $criteria['statutDA'] = StatutDaConstant::TRAITER_APPRO_LIST;
                $criteria['statutBC'] = StatutBcConstant::TRAITER_APPRO_LIST;
            }
        } else {
            if ($criteria['afficherCloturees']) {
                $criteria['statutDA'] = StatutDaConstant::TRAITER_AUTRES_LIST_CLOTURE;
            } else {
                $criteria['statutDA'] = StatutDaConstant::TRAITER_AUTRES_LIST;
            }
        }

        return $criteria;
    }

    private function appliquerVerrouillage(array $daAffichers): void
    {
        $estAdmin = $this->estAdmin();
        $estAppro = $this->estUserDansServiceAppro();
        $estAtelier = $this->estUserDansServiceAtelier();
        $estCreateur = $this->estCreateurDeDADirecte();

        foreach ($daAffichers as $daAfficher) {
            $verrouille = $this->permissionDaService->estDaVerrouillee(
                $daAfficher->getStatutDal(),
                $daAfficher->getStatutOr(),
                $estAdmin,
                $estAppro,
                $estAtelier,
                $estCreateur
            );
            $daAfficher->setVerouille($verrouille);
        }
    }

    private function TraitementFormulaireDateLivraison(Request $request, FormInterface $formDateLivraison)
    {
        $formDateLivraison->handleRequest($request);

        if ($formDateLivraison->isSubmitted() && $formDateLivraison->isValid()) {
            $data = $formDateLivraison->getData();
            $dateLivraisonPrevue = $data['dateLivraisonPrevue'];
            $daAffichers = $this->daAfficherRepository->findBy(['numeroCde' => $data['numeroCde']]);

            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setDateLivraisonPrevue($dateLivraisonPrevue)
                    ->setJoursDispo($dateLivraisonPrevue->diff(new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))->days);
                $this->getEntityManager()->persist($daAfficher);
            }

            $this->getEntityManager()->flush();
            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Date de livraison prévue modifiée avec succès']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 1, 'page' => 1]);
        }
    }

    private function initialisationRechercheDa(DaSearch $daSearch)
    {
        $criteria = $this->getSessionService()->get('criteria_search_list_da', []) ?? [];

        // Sécurité : si c'est un objet, on le convertit en tableau
        if ($criteria instanceof DaSearch) {
            $criteria = $criteria->toArray();
        }
        $criteria['afficherDaTraiter'] = true;

        $agServ = [
            'agenceEmetteur'  => isset($criteria['agenceEmetteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceEmetteur']) : null,
            'agenceDebiteur'  => isset($criteria['agenceDebiteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceDebiteur']) : null,
            'serviceEmetteur' => isset($criteria['serviceEmetteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceEmetteur']) : null,
            'serviceDebiteur' => isset($criteria['serviceDebiteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceDebiteur']) : null,
        ];
        $daSearch->toObject($criteria, $agServ);
    }
}
