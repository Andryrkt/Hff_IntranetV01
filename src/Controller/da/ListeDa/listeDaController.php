<?php

namespace App\Controller\da\ListeDa;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSearch;
use App\Form\da\DaSearchType;
use App\Mapper\Da\DaAfficherMapper;
use App\Repository\admin\AgenceRepository;
use App\Service\da\PermissionDaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use App\Service\security\SecurityService;
use App\Repository\da\DaAfficherRepository;
use App\Constants\admin\ApplicationConstant;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    // Repository et model
    private DaAfficherRepository $daAfficherRepository;
    private AgenceRepository $agenceRepository;
    private DaAfficherMapper $daAfficherMapper;
    private PermissionDaService $permissionDaService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $em = $this->getEntityManager();
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->agenceRepository = $em->getRepository(Agence::class);
        $this->daAfficherMapper = new DaAfficherMapper($this->getUrlGenerator());
        $this->permissionDaService = new PermissionDaService();
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        /** Initialisation DaSearch */
        $daSearch = new DaSearch;
        $this->initialisationRechercheDa($daSearch);

        $codeCentrale = false; // TODO : autorisation sur le code centrale

        // Agences Services autorisés sur le DAP
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DAP);
        $allAgenceServices = $this->getSecurityService()->getAllAgenceServices();

        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, [
            'method' => 'GET',
            'allAgenceServices' => $allAgenceServices
        ])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSearch $daSearch */
            $daSearch = $form->getData();
        }

        $this->gererAgenceService($daSearch, $allAgenceServices);

        $criteria = [];
        //transformer l'objet daSearch en tableau
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

        $sortJoursClass = false;

        if ($criteria && $criteria['sortNbJours']) $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        $limit = 100;

        // Agence et service par défaut
        $agenceIdUser = $this->getSecurityService()->getAgenceIdUser();
        $serviceIdUser = $this->getSecurityService()->getServiceIdUser();

        // Vérifier la permission de voir tous les données
        $multisuccursale = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);

        // Vérifier le permission de voir liste avec débiteur sur la page courante
        $peutVoirListeAvecDebiteur = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_AUTH_2);

        // Donnée à envoyer à la vue
        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA($page, $limit, $criteria, $agenceIdUser, $serviceIdUser, $codeSociete, $agenceServiceAutorises, $peutVoirListeAvecDebiteur, $multisuccursale);

        // Application du verrouillage (Logique purement applicative)
        $this->appliquerVerrouillage($paginationData['data']);

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

        /** === Formulaire pour la date de livraison prevu === */
        $formDateLivraison = $this->getFormFactory()->createBuilder(DaModalDateLivraisonType::class)->getForm();
        $this->TraitementFormulaireDateLivraison($request, $formDateLivraison);

        return $this->render('da/list-da.html.twig', [
            'data'              => $dataPrepared,
            'form'              => $form->createView(),
            'criteria'          => $criteria,
            'codeCentrale'      => $codeCentrale,
            'daTypeIcons'       => $this->presenter->getIcons(),
            'sortJoursClass'    => $sortJoursClass,
            'currentPage'       => $paginationData['currentPage'],
            'totalPages'        => $paginationData['lastPage'],
            'resultat'          => $paginationData['totalItems'],
            'formDateLivraison' => $formDateLivraison->createView(),
            'mesDaActif'        => $request->query->get('mes_da_a_traiter') == 1,
        ]);
    }

    private function TraitementFormulaireDateLivraison(Request $request, FormInterface $formDateLivraison)
    {
        $formDateLivraison->handleRequest($request);

        if ($formDateLivraison->isSubmitted() && $formDateLivraison->isValid()) {
            //recupération des valeurs dans le formulaire
            $data = $formDateLivraison->getData();
            $dateLivraisonPrevue = $data['dateLivraisonPrevue'];
            $daAffichers = $this->daAfficherRepository->findBy(['numeroCde' => $data['numeroCde']]);

            //modification de la date livraison prevue sur chaque ligne
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

    private function gererAgenceService(DaSearch $daSearch, array $allAgenceServices): void
    {
        // Changer le serviceEmetteur
        if ($daSearch->getServiceEmetteur()) {
            $ligneId = $daSearch->getServiceEmetteur();
            if ($ligneId && isset($allAgenceServices[$ligneId])) {
                $daSearch->setServiceEmetteur($allAgenceServices[$ligneId]['service_id']);
            }
        }

        // Changer le serviceDebiteur
        if ($daSearch->getServiceDebiteur()) {
            $ligneId = $daSearch->getServiceDebiteur();
            if ($ligneId && isset($allAgenceServices[$ligneId])) {
                $daSearch->setServiceDebiteur($allAgenceServices[$ligneId]['service_id']);
            }
        }
    }

    public function initialisationRechercheDa(DaSearch $daSearch)
    {
        $criteria = $this->getSessionService()->get('criteria_search_list_da', []) ?? [];

        $daSearch->toObject($criteria);
    }

    // Le verrouillage est maintenant calculé et injecté directement dans les tableaux
    private function appliquerVerrouillage(array &$daAffichers): void
    {
        $estAdmin   = false; // TODO: profil ou autre
        $estAppro   = false; // TODO: profil ou autre
        $estAtelier = false; // TODO: profil ou autre
        $estCreateur = false; // TODO: profil ou autre

        foreach ($daAffichers as &$daAfficher) {
            $daAfficher['verouille'] = $this->permissionDaService->estDaVerrouillee(
                $daAfficher['statutDal'],
                $daAfficher['statutOr'],
                $estAdmin,
                $estAppro,
                $estAtelier,
                $estCreateur
            );
        }
        unset($daAfficher); // bonne pratique après foreach par référence
    }
}
