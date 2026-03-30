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
            'estAppro' => $this->estUserDansServiceAppro()
        ])->getForm();
        $form->handleRequest($request);

        $criteria = $daSearch->toArray();

        // Gestion spécifique "Mes DA à traiter"
        if (
            empty($request->query->get('mes_da_a_traiter')) &&
            empty(array_filter($criteria, function ($value) {
                return $value !== null && $value !== false;
            }))
        ) {
            $user = $this->getUser();
            $codeAgenceUser = $user->getCodeAgenceUser();
            $codeServiceUser = $user->getCodeServiceUser();

            // On ne garde que la persistance du flag et les filtres imposés
            $criteria = [];

            if ($codeAgenceUser == '80' && $codeServiceUser == 'APP') {
                $criteria['statutDA'] = [
                    StatutDaConstant::STATUT_SOUMIS_APPRO,
                    StatutDaConstant::STATUT_DEMANDE_DEVIS,
                    StatutDaConstant::STATUT_DEVIS_A_RELANCER,
                    StatutDaConstant::STATUT_EN_COURS_PROPOSITION
                ];
                $criteria['statutBC'] = [
                    StatutBcConstant::STATUT_PAS_DANS_BC,
                    StatutBcConstant::STATUT_PAS_DANS_OR_CESSION,
                    StatutBcConstant::STATUT_A_GENERER,
                    StatutBcConstant::STATUT_CESSION_A_GENERER,
                    StatutBcConstant::STATUT_A_EDITER,
                    StatutBcConstant::STATUT_A_SOUMETTRE_A_VALIDATION,
                    StatutBcConstant::STATUT_A_ENVOYER_AU_FOURNISSEUR
                ];
            } else {
                $criteria['statutDA'] = [
                    StatutDaConstant::STATUT_EN_COURS_CREATION,
                    StatutDaConstant::STATUT_AUTORISER_EMETTEUR,
                    StatutDaConstant::STATUT_SOUMIS_ATE
                ];
            }

            $criteria['mes_da_a_traiter'] = 0;
            $this->getSessionService()->set('criteria_search_list_da_80_app', $criteria);
        } else {
            $criteria['mes_da_a_traiter'] = 1;
            // Sauvegarde classique des critères issus du formulaire
            $this->getSessionService()->set('criteria_search_list_da', $criteria);
        }

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
            $daAffichers = $this->daAfficherRepository->findBy(['numeroCde' => $data['numeroCde']]);

            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setDateLivraisonPrevue($data['dateLivraisonPrevue']);
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

        $agServ = [
            'agenceEmetteur'  => isset($criteria['agenceEmetteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceEmetteur']) : null,
            'agenceDebiteur'  => isset($criteria['agenceDebiteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceDebiteur']) : null,
            'serviceEmetteur' => isset($criteria['serviceEmetteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceEmetteur']) : null,
            'serviceDebiteur' => isset($criteria['serviceDebiteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceDebiteur']) : null,
        ];

        $daSearch->toObject($criteria, $agServ);
    }
}
