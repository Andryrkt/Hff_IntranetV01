<?php

namespace App\Controller\da\ListeDa;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaTrait;
use App\Entity\admin\Agence;
use App\Entity\admin\Application;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSearch;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use App\Form\da\DaSearchType;
use App\Repository\admin\AgenceRepository;
use App\Repository\da\DaAfficherRepository;
use App\Mapper\Da\DaAfficherMapper;
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
    private DaAfficherMapper $mapper;
    private PermissionDaService $permissionDaService;

    public function __construct()
    {
        parent::__construct();
        $em = $this->getEntityManager();
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->agenceRepository = $em->getRepository(Agence::class);
        $this->mapper = new DaAfficherMapper($this->getUrlGenerator());
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
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, ['method' => 'GET'])->getForm();
        $form->handleRequest($request);
        
        $criteria = $daSearch->toArray();
        $this->getSessionService()->set('criteria_search_list_da', $criteria);

        // Visuel de tri
        $sortJoursClass = false;
        if (!empty($criteria['sortNbJours'])) {
            $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';
        }

        // Pagination (Réduction de la limite de 500 à 20 pour la fluidité)
        $page = $request->query->getInt('page', 1);
        $limit = 50;

        // Récupération des données
        $user = $this->getUser();
        $idAgenceUser = $this->agenceRepository->findIdByCodeAgence($user->getCodeAgenceUser());
        
        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA(
            $user, $criteria, $idAgenceUser, 
            $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->estAdmin(), 
            $page, $limit
        );

        // Application du verrouillage (Logique purement applicative)
        $this->appliquerVerrouillage($paginationData['data']);

        // Préparation des données pour la vue (Via Mapper)
        $dataPrepared = $this->mapper->mapList($paginationData['data'], [
            'estAdmin'   => $this->estAdmin(),
            'estAppro'   => $this->estUserDansServiceAppro(),
            'estAtelier' => $this->estUserDansServiceAtelier()
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
            'daTypeIcons'       => $this->mapper->getIcons(),
            'sortJoursClass'    => $sortJoursClass,
            'currentPage'       => $paginationData['currentPage'],
            'totalPages'        => $paginationData['lastPage'],
            'resultat'          => $paginationData['totalItems'],
            'formDateLivraison' => $formDateLivraison->createView()
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
                $estAdmin, $estAppro, $estAtelier, $estCreateur
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
            $this->redirectToRoute("list_da");
        }
    }

    private function initialisationRechercheDa(DaSearch $daSearch)
    {
        $criteria = $this->getSessionService()->get('criteria_search_list_da', []) ?? [];
        
        $agServ = [
            'agenceEmetteur'  => isset($criteria['agenceEmetteur']) ? $this->getEntityManager()->getRepository(\App\Entity\admin\Agence::class)->find($criteria['agenceEmetteur']) : null,
            'agenceDebiteur'  => isset($criteria['agenceDebiteur']) ? $this->getEntityManager()->getRepository(\App\Entity\admin\Agence::class)->find($criteria['agenceDebiteur']) : null,
            'serviceEmetteur' => isset($criteria['serviceEmetteur']) ? $this->getEntityManager()->getRepository(\App\Entity\admin\Service::class)->find($criteria['serviceEmetteur']) : null,
            'serviceDebiteur' => isset($criteria['serviceDebiteur']) ? $this->getEntityManager()->getRepository(\App\Entity\admin\Service::class)->find($criteria['serviceDebiteur']) : null,
        ];

        $daSearch->toObject($criteria, $agServ);
    }
}
