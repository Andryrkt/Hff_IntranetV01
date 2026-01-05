<?php

namespace App\Controller\planning;


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Model\planning\PlanningModel;
use App\Entity\planning\PlanningSearch;
use App\Service\TableauEnStringService;
use App\Controller\Traits\PlanningTraits;
use App\Controller\Traits\Transformation;
use App\Form\planning\PlanningSearchType;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\historiqueOperation\HistoriqueOperationDITService;
use Symfony\Component\Form\FormInterface;

/**
 * @Route("/atelier")
 */
class PlanningController extends Controller
{
    use Transformation;
    use PlanningTraits;
    use AutorisationTrait;

    private PlanningModel $planningModel;
    private PlanningSearch $planningSearch;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->planningModel = new PlanningModel();
        $this->planningSearch = new PlanningSearch();
        $this->ditOrsSoumisAValidationRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
    }

    /**
     * @Route("/planning-vue", name="planning_vue")
     * 
     * @return void
     */
    public function listePlanning(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_REP);
        /** FIN AUtorisation acées */

        //initialisation
        $this->planningSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
        ;

        $form = $this->getFormFactory()->createBuilder(
            PlanningSearchType::class,
            $this->planningSearch,
            [
                'method' => 'GET',
                'planningDetaille' => false,
            ]
        )->getForm();

        //traitement du formulaire
        $criteria = $this->traitementFormulaire($form, $request);

        /**
         * Transformation du critère en tableau
         */
        $criteriaTAb = [];
        //transformer l'objet ditSearch en tableau
        $criteriaTAb = $criteria->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->getSessionService()->set('planning_search_criteria', $criteriaTAb);


        if ($request->query->get('action') !== 'oui') {
            /** @var string $orAvecItv @var string $orSansItv */
            ['orAvecItv' => $orAvecItv, 'orSansItv' => $orSansItv] = $this->recupNumOrValider($criteria);
            $tousLesOrSoumis = $this->allOrs();
            $touslesOrItvSoumis = $this->allOrsItv();

            $back = $this->planningModel->backOrderPlanning($orSansItv, $criteria, $tousLesOrSoumis);
            $backString = is_array($back) ? TableauEnStringService::orEnString($back) : '';

            $data = $this->planningModel->recuperationMaterielplanifier($criteria, $orAvecItv, $backString, $touslesOrItvSoumis);
        } else {
            $data = [];
            $back = [];
        }

        $tabObjetPlanning = $this->creationTableauObjetPlanning($data, $back, $this->getEntityManager());
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = $this->ajoutMoiDetail($tabObjetPlanning);

        $forDisplay = $this->prepareDataForDisplay($fusionResult, $criteria->getMonths() == null ? 3 : $criteria->getMonths());

        // dd($forDisplay);
        $this->logUserVisit('planning_vue'); // historisation du page visité par l'utilisateur

        return $this->render('planning/planning.html.twig', [
            'form' => $form->createView(),
            'preparedData' => $forDisplay['preparedData'],
            'uniqueMonths' => $forDisplay['uniqueMonths'],
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request): PlanningSearch
    {
        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->planningSearch;

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getdata());
            $criteria =  $form->getdata();
        }

        return $criteria;
    }


    private function allOrsItv()
    {
        /** @var array */
        $numOrItv = $this->ditOrsSoumisAValidationRepository->findNumOrItvAll();
        return TableauEnStringService::TableauEnString(',', $numOrItv);
    }

    private function allOrs()
    {
        /** @var array */
        $numOrs = $this->ditOrsSoumisAValidationRepository->findNumOrAll();
        return TableauEnStringService::TableauEnString(',', $numOrs);
    }

    /**
     * @Route("/export_excel_planning", name= "export_planning")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->getSessionService()->get('planning_search_criteria');

        $planningSearch = $this->creationObjetCriteria($criteria);

        $lesOrvalides = $this->recupNumOrValider($planningSearch);

        $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv'], $criteria, $this->allOrs());
        $data = $this->planningModel->exportExcelPlanning($planningSearch, $lesOrvalides['orAvecItv']);



        $tabObjetPlanning = $this->creationTableauObjetPlanning($data, $back, $this->getEntityManager());
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = $this->ajoutMoiDetail($tabObjetPlanning);



        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['Agence\Service', 'ID', 'Marque', 'Modèle', 'N°Serie', 'N°Parc', 'Casier', 'Jan', 'Fév', 'Mar',  'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']; // En-têtes des colonnes
        foreach ($fusionResult as $entity) {
            $row = [
                $entity->getLibsuc() . ' - ' . $entity->getLibServ(),
                $entity->getIdMat(),
                $entity->getMarqueMat(),
                $entity->getTypeMat(),
                $entity->getnumSerie(),
                $entity->getnumParc(),
                $entity->getCasier(),
            ];

            // Initialiser les mois avec des valeurs par défaut
            $moisData = array_fill(1, 12, '-');

            // Ajouter les données des mois disponibles
            foreach ($entity->getMoisDetails() as $value) {
                if (isset($value['mois'], $value['orIntv']) && $value['mois'] >= 1 && $value['mois'] <= 12) {
                    if ($moisData[$value['mois']] !== '-') {
                        $moisData[$value['mois']] .= "  " . $value['orIntv']; // Ajout d'un saut de ligne et de la nouvelle valeur
                    } else {
                        $moisData[$value['mois']] = $value['orIntv']; // Nouvelle valeur
                    }
                }
            }

            // Fusionner les données générales avec celles des mois
            $data[] = array_merge($row, $moisData);
        }

        $this->getExcelService()->createSpreadsheet($data);
    }




    /**
     * @Route("/export_excel_planning01", name= "export_planning01")
     */
    public function exportExcel01()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->getSessionService()->get('planning_search_criteria');

        $planningSearch = $this->creationObjetCriteria($criteria);

        $lesOrvalides = $this->recupNumOrValider($planningSearch);

        $data = $this->planningModel->exportExcelPlanning($planningSearch, $lesOrvalides['orAvecItv']);
        //  dd($data);

        $tabObjetPlanning = $this->creationTableauObjetExport($data);



        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['Agence\Service', 'N°OR-Itv', 'libellé de l\'Itv', 'planification', 'ID', 'Marque', 'Modèle', 'N°Serie', 'N°Parc', 'Casier', 'Mois planning', 'Année planning', 'Statut IPS', 'COMMENTAIRE ICI', 'ACTION']; // En-têtes des colonnes
        foreach ($tabObjetPlanning as $entity) {
            $data[] = [
                $entity->getLibsuc() . ' - ' . $entity->getLibServ(),
                $entity->getOrIntv(),
                $entity->getCommentaire(),
                $entity->getPlan(),
                $entity->getIdMat(),
                $entity->getMarqueMat(),
                $entity->getTypeMat(),
                $entity->getnumSerie(),
                $entity->getnumParc(),
                $entity->getCasier(),
                $entity->getMois(),
                $entity->getAnnee(),
                $entity->getPos()

            ];
        }

        $this->getExcelService()->createSpreadsheet($data);
    }
}
