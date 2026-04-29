<?php

namespace App\Controller\da\ListeDa;


use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSearch;
use App\Entity\ddp\DemandePaiement;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use App\Form\da\DaSearchType;
use App\Mapper\Da\DaAfficherMapper;
use App\Repository\da\DaAfficherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    // Repository et model
    private DaAfficherRepository $daAfficherRepository;
    private DaAfficherMapper $daAfficherMapper;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->daAfficherRepository = $entityManager->getRepository(DaAfficher::class);
        $this->daAfficherMapper = new DaAfficherMapper($this->getUrlGenerator(), $this->getEntityManager());
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        // Agences Services autorisés sur le DAP
        $allAgenceServices = $this->getSecurityService()->getAllAgenceServices();
        // Agence et service par défaut
        $agenceIdUser = $this->getSecurityService()->getAgenceIdUser();
        $serviceIdUser = $this->getSecurityService()->getServiceIdUser();

        /** Initialisation DaSearch */
        $daSearch = new DaSearch;
        $this->initialisationRechercheDa($daSearch);

        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, [
            'method' => 'GET',
            'estAppro' => $this->estAppro(),
            'allAgenceServices' => $allAgenceServices,
            'codeAgence'  => $agenceIdUser,
            'codeService' => $serviceIdUser,
        ])->getForm();
        $criteria = $this->traitementFormualireRecherche($request, $form, $daSearch);

        $sortJoursClass = false;

        if ($criteria && !empty($criteria['sortNbJours'])) $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        $limit = 100;

        // Donnée à envoyer à la vue
        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA($page, $limit, $criteria, $agenceIdUser, $serviceIdUser, $codeSociete);

        // Préparation des données pour la vue (Via Presenter avec Cache)
        $dataPrepared = $this->daAfficherMapper->mapList($paginationData['data'], [
            'estAdmin'   => $this->estAdmin(),
            'estAppro'   => $this->estAppro(),
            'estAtelier' => $this->estAtelier(),
            'estCreateur' => $this->estCreateurDaDirecte(),
            'demandePaiementRepository' => $this->getEntityManager()->getRepository(DemandePaiement::class),
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
            'codeCentrale'      => $this->estAdmin() || $this->estEnergie(),
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

            // Sauvegarde classique des critères issus du formulaire
            $this->getSessionService()->set('criteria_search_list_da', $criteria);
        }
        // Gestion spécifique "Mes DA à traiter"
        else {
            $criteria = $daSearch->toArray();

            $this->getSessionService()->set('criteria_search_list_da_80_app', $criteria);
        }



        return $criteria;
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



    public function initialisationRechercheDa(DaSearch $daSearch)
    {
        $criteria = $this->getSessionService()->get('criteria_search_list_da', []) ?? [];

        // Sécurité : si c'est un objet, on le convertit en tableau
        if ($criteria instanceof DaSearch) {
            $criteria = $criteria->toArray();
        }
        // On ne met à true que si la clé n'existe pas (première visite)
        if (!isset($criteria['afficherDaTraiter'])) {
            $criteria['afficherDaTraiter'] = true;
        }

        $agServ = [
            'agenceEmetteur'  => isset($criteria['agenceEmetteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceEmetteur']) : null,
            'agenceDebiteur'  => isset($criteria['agenceDebiteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceDebiteur']) : null,
            'serviceEmetteur' => isset($criteria['serviceEmetteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceEmetteur']) : null,
            'serviceDebiteur' => isset($criteria['serviceDebiteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceDebiteur']) : null,
        ];
        $daSearch->toObject($criteria, $agServ);
    }
}
