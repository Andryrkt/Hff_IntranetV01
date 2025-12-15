<?php

namespace App\Controller\da\ListeDa;

use App\Model\da\DaModel;
use App\Utils\PerfLogger;
use App\Entity\da\DaSearch;
use App\Entity\da\DaAfficher;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Entity\admin\utilisateur\Role;
use App\Service\da\DemandeApproService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use App\Controller\Traits\da\DaListeTrait;
use App\Repository\admin\AgenceRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Agence;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\dossierInterventionAtelierModel;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaListeTrait;
    use AutorisationTrait;
    private $perfLogger;
    private DemandeApproService $demandeApproService;

    // Repository et model
    private DaModel $daModel;
    private dossierInterventionAtelierModel $dwModel;
    private AgenceRepository $agenceRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaAfficherRepository $daAfficherRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DaModel $daModel,
        dossierInterventionAtelierModel $dwModel,
        DemandeApproService $demandeApproService
    ) {
        $this->perfLogger = PerfLogger::getInstance();
        $this->perfLogger->log('constructeur du controleur listeDaController', __FILE__);
        parent::__construct();
        $this->perfLogger->log('constructeur du parent Controleur', __FILE__);

        $this->daModel                           = $daModel;
        $this->dwModel                           = $dwModel;
        $this->demandeApproService               = $demandeApproService;
        $this->agenceRepository                  = $entityManager->getRepository(Agence::class);
        $this->daSoumissionBcRepository          = $entityManager->getRepository(DaSoumissionBc::class);
        $this->ditOrsSoumisAValidationRepository = $entityManager->getRepository(DitOrsSoumisAValidation::class);
        $this->daAfficherRepository              = $entityManager->getRepository(DaAfficher::class);

        $this->initStatutBcTrait();
        $this->perfLogger->log('initialisation du trait StatutBcTrait', __FILE__);
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        $this->perfLogger->log('debut de la fonction index', __FILE__);
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $this->perfLogger->log('verification si user connecter', __FILE__);

        /** Autorisation accès */
        $this->autorisationAcces(Application::ID_DAP);
        $this->perfLogger->log('Autorisation acces', __FILE__);
        /** FIN AUtorisation accès */

        /** Initialisation DaSearch */
        $daSearch = new DaSearch;
        $this->initialisationRechercheDa($daSearch);
        $this->perfLogger->log('Initialisation DaSearch', __FILE__);

        $codeCentrale = $this->hasRoles(Role::ROLE_ADMINISTRATEUR) || in_array($this->getCodeAgenceUser(), ['90', '91', '92']);
        $this->perfLogger->log('Initialisation agence', __FILE__);

        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, ['method' => 'GET'])->getForm();
        $this->perfLogger->log('formulaire de recherche', __FILE__);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSearch $daSearch */
            $daSearch = $form->getData();
        }
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

        $this->perfLogger->log('recupération du numero de page et nombre de ligne par page', __FILE__);
        // Donnée à envoyer à la vue
        $paginationData = $this->getPaginationData($criteria, $page, $limit);
        $this->perfLogger->log('récupération des données à envoyer à la vue', __FILE__);
        $dataPrepared = $this->prepareDataForDisplay($paginationData['data']);
        $this->perfLogger->log('préparation des données à envoyer à la vue', __FILE__);

        /** === Formulaire pour la date de livraison prevu === */
        $formDateLivraison = $this->getFormFactory()->createBuilder(DaModalDateLivraisonType::class)->getForm();
        $this->perfLogger->log('création du formulaire de date de livraison', __FILE__);
        $this->TraitementFormulaireDateLivraison($request, $formDateLivraison);
        $this->perfLogger->log('traitement du formulaire de date de livraison', __FILE__);

        return $this->render('da/list-da.html.twig', [
            'data'           => $dataPrepared,
            'form'           => $form->createView(),
            'criteria'       => $criteria,
            'codeCentrale'   => $codeCentrale,
            'daTypeIcons'    => $this->getAllIcons(),
            'sortJoursClass' => $sortJoursClass,
            'currentPage'    => $paginationData['currentPage'],
            'totalPages'     => $paginationData['lastPage'],
            'resultat'       => $paginationData['totalItems'],
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
}
