<?php

namespace App\Controller\da\ListeDa;

use App\Entity\da\DaSearch;
use App\Form\da\DaSearchType;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Service\da\DaListePresenter;
use App\Service\da\PermissionDaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use App\Service\security\SecurityService;
use App\Repository\da\DaAfficherRepository;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    // Repository et model
    private DaAfficherRepository $daAfficherRepository;
    private DaListePresenter $presenter;
    private PermissionDaService $permissionDaService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->daAfficherRepository              = $entityManager->getRepository(DaAfficher::class);
        $this->presenter                         = new DaListePresenter($this->getUrlGenerator());
        $this->permissionDaService               = new PermissionDaService();
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
        //recupères les données du criteria dans une session nommé criteria_search_list_da
        $this->getSessionService()->set('criteria_search_list_da', $criteria);

        $sortJoursClass = false;

        if ($criteria && $criteria['sortNbJours']) $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        // Agence et service par défaut
        $agenceIdUser = $this->getSecurityService()->getAgenceIdUser();
        $serviceIdUser = $this->getSecurityService()->getServiceIdUser();

        // Vérifier le permission de voir liste avec débiteur sur la page courante
        $peutVoirListeAvecDebiteur = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_AUTH_2);

        // Donnée à envoyer à la vue
        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA($page, $limit, $criteria, $agenceIdUser, $serviceIdUser, $codeSociete, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);

        // Application du verrouillage (Logique purement applicative)
        $this->appliquerVerrouillage($paginationData['data']);

        // Préparation des données pour la vue (Via Presenter avec Cache)
        $dataPrepared = $this->presenter->present($paginationData['data'], [
            'estAdmin'   => false, // TODO: profil à faire
            'estAppro'   => false, // TODO: profil à faire
            'estAtelier' => false // TODO: profil à faire
        ]);

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
            'formDateLivraison' => $formDateLivraison->createView()
        ]);
    }

    private function TraitementFormulaireDateLivraison(Request $request, FormInterface $formDateLivraison)
    {
        $formDateLivraison->handleRequest($request);

        if ($formDateLivraison->isSubmitted() && $formDateLivraison->isValid()) {
            //recupération des valeurs dans le formulaire
            $data = $formDateLivraison->getData();

            // recupération des lignes de commande dans le da_afficher
            $daAffichers = $this->getEntityManager()->getRepository(DaAfficher::class)->findBy(['numeroCde' => $data['numeroCde']]);

            //modification de la date livraison prevue sur chaque ligne
            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setDateLivraisonPrevue($data['dateLivraisonPrevue']);
                $this->getEntityManager()->persist($daAfficher);
            }

            $this->getEntityManager()->flush();

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Date de livraison prévue modifier avec succèss']);
            $this->redirectToRoute("da_list_cde_frn");
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
