<?php


namespace App\Controller\dit;


use DateTime;
use App\Entity\dit\DitSearch;
use App\Service\ExcelService;
use App\Controller\Controller;
use App\Form\dit\DitSearchType;
use App\Form\dit\DocDansDwType;
use App\Model\dit\DitListModel;
use App\Entity\admin\Application;
use App\Service\Users\UserDataService;
use App\Controller\Traits\dit\DitListTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationDITService;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitListeController extends Controller
{
    use DitListTrait;
    use AutorisationTrait;

    private $historiqueOperation;
    private UserDataService $userDataService;
    private $excelService;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager(), $this->getSessionService());
        $this->userDataService = new UserDataService($this->getEntityManager());
        $this->excelService = $this->getService(ExcelService::class);
    }

    /**
     * @Route("/dit-liste", name="dit_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recuperation agence et service autoriser
        $agenceIds = $this->getUser()->getAgenceAutoriserIds();
        $serviceIds = $this->getUser()->getServiceAutoriserIds();

        /** CREATION D'AUTORISATION */
        $this->autorisationAcces($this->getUser(), Application::ID_DIT);
        $autoriser = $this->autorisationRole($this->getEntityManager());
        $autorisationRoleEnergie = $this->autorisationRoleEnergie($this->getEntityManager());
        //FIN AUTORISATION

        $ditListeModel = $this->getService(DitListModel::class);
        $ditSearch = new DitSearch();
        $agenceServiceIps = $this->agenceServiceIpsObjet();

        $this->initialisationRechercheDit($ditSearch, $this->getEntityManager(), $agenceServiceIps, $autoriser);

        //création et initialisation du formulaire de la recherche
        $form = $this->getFormFactory()->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            //'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId(),
            'autorisationRoleEnergie' => $autorisationRoleEnergie
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData();
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
            if (!empty($numParc) || !empty($numSerie)) {
                $idMateriel = $this->getDitModel()->recuperationIdMateriel($numParc, strtoupper($numSerie));
                if (!empty($idMateriel)) {
                    $this->ajoutDonnerRecherche($form, $ditSearch);
                    $ditSearch->setIdMateriel($idMateriel[0]['num_matricule']);
                }
            } else {
                $this->ajoutDonnerRecherche($form, $ditSearch);
                $ditSearch->setIdMateriel($form->get('idMateriel')->getData());
            }
        }


        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $ditSearch->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->getSessionService()->set('dit_search_criteria', $criteria);


        $agenceServiceEmetteur = $this->agenceServiceEmetteur($agenceServiceIps, $autoriser);
        $option = $this->Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds);
        $this->getSessionService()->set('dit_search_option', $option);

        //recupération des donnée
        $paginationData = $this->data($request, $ditListeModel, $ditSearch, $option, $this->getEntityManager());

        /**  Docs à intégrer dans DW * */
        $formDocDansDW = $this->getFormFactory()->createBuilder(DocDansDwType::class, null, [
            'method' => 'GET',
        ])->getForm();

        // $this->dossierDit($request, $formDocDansDW);
        $formDocDansDW->handleRequest($request);

        if ($formDocDansDW->isSubmitted() && $formDocDansDW->isValid()) {
            if ($formDocDansDW->getData()['docDansDW'] === 'OR') {
                $this->redirectToRoute("dit_insertion_or", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            } elseif ($formDocDansDW->getData()['docDansDW'] === 'FACTURE') {
                $this->redirectToRoute("dit_insertion_facture", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            } elseif ($formDocDansDW->getData()['docDansDW'] === 'RI') {
                $this->redirectToRoute("dit_insertion_ri", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            } elseif ($formDocDansDW->getData()['docDansDW'] === 'DEVIS-VP') {
                $this->redirectToRoute("dit_insertion_devis", ['numDit' => $formDocDansDW->getData()['numeroDit'], 'type' => 'VP']);
            } elseif ($formDocDansDW->getData()['docDansDW'] === 'DEVIS-VA') {
                $this->redirectToRoute("dit_insertion_devis", ['numDit' => $formDocDansDW->getData()['numeroDit'], 'type' => 'VA']);
            } elseif ($formDocDansDW->getData()['docDansDW'] === 'BC') {
                $this->redirectToRoute("dit_ac_bc_soumis", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            }
        }

        /** HISTORIQUE DES OPERATION */
        // Filtrer les critères pour supprimer les valeurs "falsy"
        $filteredCriteria = $this->criteriaTab($criteria);

        // Déterminer le type de log
        $logType = empty($filteredCriteria) ? ['dit_index'] : ['dit_index_search', $filteredCriteria];

        // Appeler la méthode logUserVisit avec les arguments définis
        $this->logUserVisit(...$logType);


        return $this->render('dit/list.html.twig', [
            'data'          => $paginationData['data'],
            'currentPage'   => $paginationData['currentPage'],
            'totalPages'    => $paginationData['lastPage'],
            'criteria'      => $criteria,
            'resultat'      => $paginationData['totalItems'],
            'statusCounts'  => $paginationData['statusCounts'],
            'form'          => $form->createView(),
            'formDocDansDW' => $formDocDansDW->createView()
        ]);
    }

    private function updateNumeroDevis(array $paginationData, DitListModel $ditListModel): array
    {
        foreach ($paginationData['data'] as $item) {
            if ($item->getInternetExterne() === 'EXTERNE' && (is_null($item->getNumeroDevisRattache()) || empty($item->getNumeroDevisRattache()))) {
                // Récupération du numéro de devis
                $numeroDevisModel = $ditListModel->recupNumeroDevis($item->getNumeroDemandeIntervention());

                // Vérification de la récupération du numéro de devis
                $numeroDevis = !empty($numeroDevisModel) ? $numeroDevisModel[0]['numdevis'] : null;

                // Mise à jour de l'élément avec le numéro de devis
                $item->setNumeroDevisRattache($numeroDevis);

                $this->getEntityManager()->persist($item);
            }
        }
        $this->getEntityManager()->flush();

        return $paginationData;
    }

    /**
     * @Route("/export-excel", name="export_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('dit_search_criteria', []);
        //recupère les critères dans la session 
        $options = $this->getSessionService()->get('dit_search_option', []);

        //crée une objet à partir du tableau critère reçu par la session
        $ditSearch = $this->transformationEnObjet($criteria);

        $entities = $this->DonnerAAjouterExcel($ditSearch, $options, $this->getEntityManager());

        // Convertir les entités en tableau de données
        $data = $this->transformationEnTableauAvecEntet($entities);
        //creation du fichier excel
        $this->getExcelService()->createSpreadsheet($data);
    }


    private function criteriaTab(array $criteria): array
    {
        $criteriaTab = $criteria;

        $criteriaTab['typeDocument']    = $criteria['typeDocument'] ? $criteria['typeDocument']->getDescription() : $criteria['typeDocument'];
        $criteriaTab['niveauUrgence']   = $criteria['niveauUrgence'] ? $criteria['niveauUrgence']->getDescription() : $criteria['niveauUrgence'];
        $criteriaTab['statut']          = $criteria['statut'] ? $criteria['statut']->getDescription() : $criteria['statut'];
        $criteriaTab['dateDebut']       = $criteria['dateDebut'] ? $criteria['dateDebut']->format('d-m-Y') : $criteria['dateDebut'];
        $criteriaTab['dateFin']         = $criteria['dateFin'] ? $criteria['dateFin']->format('d-m-Y') : $criteria['dateFin'];
        $criteriaTab['agenceEmetteur']  = $criteria['agenceEmetteur'] ? $criteria['agenceEmetteur']->getLibelleAgence() : $criteria['agenceEmetteur'];
        $criteriaTab['serviceEmetteur'] = $criteria['serviceEmetteur'] ? $criteria['serviceEmetteur']->getLibelleService() : $criteria['serviceEmetteur'];
        $criteriaTab['agenceDebiteur']  = $criteria['agenceDebiteur'] ? $criteria['agenceDebiteur']->getLibelleAgence() : $criteria['agenceDebiteur'];
        $criteriaTab['serviceDebiteur'] = $criteria['serviceDebiteur'] ? $criteria['serviceDebiteur']->getLibelleService() : $criteria['serviceDebiteur'];
        $criteriaTab['categorie']       = $criteria['categorie'] ? $criteria['categorie']->getLibelleCategorieAteApp() : $criteria['categorie'];

        // Filtrer les critères pour supprimer les valeurs "falsy"
        return  array_filter($criteriaTab);
    }
}
