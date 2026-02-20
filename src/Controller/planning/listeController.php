<?php

namespace App\Controller\planning;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Model\planning\PlanningModel;
use App\Entity\planning\PlanningSearch;
use App\Service\TableauEnStringService;
use App\Controller\Traits\PlanningTraits;
use App\Controller\Traits\Transformation;
use App\Form\planning\PlanningSearchType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\historiqueOperation\HistoriqueOperationDITService;

/**
 * @Route("/atelier")
 */
class ListeController extends Controller
{
    use Transformation;
    use PlanningTraits;
    private PlanningSearch $planningSearch;
    private PlanningModel $planningModel;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->planningSearch = new PlanningSearch();
        $this->planningModel = new PlanningModel();
        $this->ditOrsSoumisAValidationRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
    }
    /**
     * @Route("/planning-detaille",name = "liste_planning")
     * 
     *@return void
     */
    public function listecomplet(Request $request)
    {
        $resultat = 0;
        $pagesCount = 0;

        //initialisation
        $this->conditionFormulaireRecherche();

        $form = $this->getFormFactory()->createBuilder(
            PlanningSearchType::class,
            $this->planningSearch,
            [
                'method' => 'GET',
                'planningDetaille' => true,
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->planningSearch;

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getdata();
        }

        /**
         * Transformation du critère en tableau
         */
        $criteriaTAb = [];
        //transformer l'objet ditSearch en tableau
        $criteriaTAb = $criteria->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->getSessionService()->set('planning_search_criteria', $criteriaTAb);

        $data = ['data' => []];
        $count = [];
        if ($request->query->get('action') !== 'oui') {

            $lesOrvalides = $this->recupNumOrValider($criteria);
            // dump($lesOrvalides['orSansItv']);
            $tousLesOrSoumis = $this->allOrs();
            $touslesOrItvSoumis = $this->allOrsItv();
            $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv'], $criteria, $tousLesOrSoumis);
            if (is_array($back)) {
                $backString = TableauEnStringService::orEnString($back);
            } else {
                $backString = '';
            }
            $result = $this->planningModel->recupMatListeTous($criteria, $lesOrvalides['orAvecItv'], $backString, $tousLesOrSoumis);
            $data = $this->recupData($result, $back);
            //    dump($data);
            $count = $this->planningModel->recupMatListeTousCount($criteria, $lesOrvalides['orAvecItv'], $backString, $tousLesOrSoumis);
            $this->getSessionService()->set('data_planning_detail_excel', $data['data_excel']);
            // dump($data['data'], $data['data_excel']);
        }
        return $this->render('planning/listePlanning.html.twig', [
            'form' => $form->createView(),
            'criteria' => $criteriaTAb,
            'data' => $data['data'],
            'count' => $count
        ]);
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
     * @Route("/export_excel_liste_planning", name= "export_liste_planning")
     */
    public function exportExcel()
    {
        $data = $this->getSessionService()->get('data_planning_detail_excel');
        $header = [
            'agenceServiceTravaux' => 'Agence - Service',
            'Marque' => 'Marque',
            'Modele' => 'Modèle',
            'Id' => 'ID',
            'N_Serie' => 'N° Série',
            'parc' => 'Parc',
            'casier' => 'Casier',
            'commentaire' => 'Intitulé',
            'numor_itv' => 'Num OR - ITV',
            'dateplanning' => 'Date Planning',
            'cst' => 'CST',
            'ref' => 'Référence',
            'desi' => 'Désignation',
            'qteres_or' => 'Qte Res OR',
            'qteall_or' => 'Qte All OR',
            'qtereliquat' => 'Qte Reliquat',
            'qteliv_or' => 'Qte Livrée OR',
            'statutOR' => 'Statut OR',
            'datestatutOR' => 'Date statut OR',
            'ctr_marque' => 'Ctr Marque ',
            'numerocmd' => 'Numéro CMD',
            'statut_ctrmq' => 'Statut CTRMQ',
            'numcis' => 'Numéro CIS',
            'qteORlig_cis' => 'Qte OR CIS',
            'qtealllig_cis' => 'Qte All CIS',
            'qterlqlig_cis' => 'Qte Reliquat CIS',
            'qtelivlig_cis' => 'Qte Livrée CIS',
            'statutCis' => 'Statut CIS',
            'datestatutCis' => 'Date Statut CIS',
            'Eta_ivato' => 'État Ivato',
            'Eta_magasin' => 'État Magasin',
            'message' => 'Message',
            'ord' => 'Commande Envoyé',
            'status_b' => 'Statut'
        ];
        array_unshift($data, $header);
        $this->exporterDonneesExcel($data);
    }

    private function exporterDonneesExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajout des données
        $rowIndex = 1;
        foreach ($data as $row) {
            $sheet->fromArray([$row], null, "A$rowIndex");
            $rowIndex++;
        }

        // Téléchargement du fichier
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export.xlsx"');
        $writer->save('php://output');
        exit();
    }

    private function conditionFormulaireRecherche()
    {
        $this->planningSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
        ;

        $criteria = $this->getSessionService()->get('planning_search_criteria');

        if (!empty($criteria)) {
            $this->planningSearch
                ->setAgence($criteria['agence'])
                ->setAnnee($criteria['annee'])
                ->setInterneExterne($criteria['interneExterne'])
                ->setFacture($criteria['facture'])
                ->setPlan($criteria['plan'])
                ->setDateDebut($criteria['dateDebut'])
                ->setDateFin($criteria['dateFin'])
                ->setNumOr($criteria['numOr'])
                ->setNumSerie($criteria['numSerie'])
                ->setIdMat($criteria['idMat'])
                ->setNumParc($criteria['numParc'])
                ->setAgenceDebite($criteria['agenceDebite'])
                ->setServiceDebite($criteria['serviceDebite'])
                ->setTypeLigne($criteria['typeligne'])
                ->setOrBackOrder($criteria['orBackOrder'])
            ;
        }
    }



    public function recupData($result, $back, $sendCmd = false, $excelBack = false)
    {
        $data = [];
        $data_excel = [];

        if (!empty($result)) {
            // dump($result);
            $qteCis = [];
            $dateLivLigCIS = [];
            $dateAllLigCIS = [];
            for ($i = 0; $i < count($result); $i++) {
                $orItv = $result[$i]['orintv'];
                if (in_array($orItv, $back)) {
                    $result[$i]['backOrder'] = $excelBack === false ? 'back' : '';
                } else {
                    $result[$i]['backOrder'] = $excelBack === false ? 'not' : '';
                }
                if (substr($result[$i]['numor'], 0, 1) == '5') {
                    if ($result[$i]['numcis'] !== "0" || $result[$i]['numerocdecis'] == "0") {
                        $recupGcot = [];
                        $qteCis[] = $this->planningModel->recupeQteCISlig($result[$i]['numor'], $result[$i]['itv'], $result[$i]['ref']);
                        $dateLivLigCIS[] = $this->planningModel->dateLivraisonCIS($result[$i]['numcis'], $result[$i]['ref'], $result[$i]['cst']);
                        $dateAllLigCIS[] = $this->planningModel->dateAllocationCIS($result[$i]['numcis'], $result[$i]['ref'], $result[$i]['cst']);
                        $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($result[$i]['numerocdecis']);
                    } else {
                        $etatMag[] = $this->planningModel->recuperationEtaMag($result[$i]['numerocdecis'], $result[$i]['ref'], $result[$i]['cst']);
                        $qteCis[] = $this->planningModel->recupeQteCISlig($result[$i]['numor'], $result[$i]['itv'], $result[$i]['ref']);
                        $dateLivLigCIS[] = $this->planningModel->dateLivraisonCIS($result[$i]['numcis'], $result[$i]['ref'], $result[$i]['cst']);
                        $dateAllLigCIS[] = $this->planningModel->dateAllocationCIS($result[$i]['numcis'], $result[$i]['ref'], $result[$i]['cst']);
                        $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($result[$i]['numerocdecis']);
                        $recupPartiel[] = $this->planningModel->recuperationPartiel($result[$i]['numerocdecis'], $result[$i]['ref']);
                    }
                } else {
                    if (empty($result[$i]['numerocmd']) || $result[$i]['numerocmd'] == '0') {
                        $recupGcot = [];
                    } else {
                        $recupPartiel[] = $this->planningModel->recuperationPartiel($result[$i]['numerocmd'], $result[$i]['ref']);
                        $etatMag[] = $this->planningModel->recuperationEtaMag($result[$i]['numerocmd'], $result[$i]['ref'], $result[$i]['cst']);
                        $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($result[$i]['numerocmd']);
                    }
                }


                if (!empty($etatMag[0])) {
                    $result[$i]['Eta_ivato'] = $etatMag[0][0]['Eta_ivato'];
                    $result[$i]['Eta_magasin'] =  $etatMag[0][0]['Eta_magasin'];
                    $etatMag = [];
                } else {
                    $result[$i]['Eta_ivato'] = "";
                    $result[$i]['Eta_magasin'] = "";
                    $etatMag = [];
                }

                if (!empty($recupPartiel[$i])) {
                    $result[$i]['qteSlode'] = $recupPartiel[$i]['0']['solde'];
                    $result[$i]['qte'] = $recupPartiel[$i]['0']['qte'];
                } else {
                    $result[$i]['qteSlode'] = "";
                    $result[$i]['qte'] = "";
                }

                if (!empty($recupGcot)) {
                    $result[$i]['Ord'] = $recupGcot['ord'] === false ? '' : ($sendCmd === false ? $recupGcot['ord']['Ord'] : "oui");
                } else {
                    $result[$i]['Ord'] = "";
                }

                if (!empty($dateLivLigCIS[$i][0])) {
                    $result[$i]['dateLivLIg'] = $dateLivLigCIS[$i]['0']['datelivlig'];
                } else {
                    $result[$i]['dateLivLIg'] = "";
                }

                if (!empty($dateAllLigCIS)) {
                    $result[$i]['dateAllLIg'] = $dateAllLigCIS[0]['0']['datealllig'];
                } else {
                    $result[$i]['dateAllLIg'] = "";
                }

                if (!empty($qteCis)) {
                    if (!empty($qteCis[$i])) {
                        $result[$i]['qteORlig'] = $qteCis[$i]['0']['qteorlig'];
                        $result[$i]['qtealllig'] = $qteCis[$i]['0']['qtealllig'];
                        $result[$i]['qterlqlig'] = $qteCis[$i]['0']['qtereliquatlig'];
                        $result[$i]['qtelivlig'] = $qteCis[$i]['0']['qtelivlig'];
                    } elseif (isset($qteCis[$i - 1]) && !empty($qteCis[$i - 1])) {
                        $result[$i]['qteORlig'] = $qteCis[$i - 1]['0']['qteorlig'];
                        $result[$i]['qtealllig'] = $qteCis[$i - 1]['0']['qtealllig'];
                        $result[$i]['qterlqlig'] = $qteCis[$i - 1]['0']['qtereliquatlig'];
                        $result[$i]['qtelivlig'] = $qteCis[$i - 1]['0']['qtelivlig'];
                    } else {
                        $result[$i]['qteORlig'] = "";
                        $result[$i]['qtealllig'] = "";
                        $result[$i]['qterlqlig'] = "";
                        $result[$i]['qtelivlig'] = "";
                    }
                } else {
                    $result[$i]['qteORlig'] = "";
                    $result[$i]['qtealllig'] = "";
                    $result[$i]['qterlqlig'] = "";
                    $result[$i]['qtelivlig'] = "";
                }
                if ($result[$i]['qtelivlig'] > 0 &&  $result[$i]['qtealllig']  == 0 && $result[$i]['qterlqlig'] == 0) {
                    $result[$i]['StatutCIS'] = "LIVRE";
                    $result[$i]['DateStatutCIS'] = $result[$i]['dateLivLIg'];
                } elseif ($result[$i]['qtealllig'] > 0) {
                    $result[$i]['StatutCIS'] = "A LIVRER";
                    $result[$i]['DateStatutCIS'] = $result[$i]['dateAllLIg'];
                } else {
                    $result[$i]['StatutCIS'] = "";
                    $result[$i]['DateStatutCIS'] = "";
                }
                // dump($i, $result[$i]['numcis'] , $result[$i]['numerocmd'] );
                if (substr($result[$i]['numcis'], 0, 1) !== '1') {
                    $result[$i]['numcde_cis'] = $result[$i]['numcis'];
                    $result[$i]['numcisOR'] = '';
                } else {
                    $result[$i]['numcde_cis'] = $result[$i]['numcis'];
                    $result[$i]['numcisOR'] = $result[$i]['numcis'];
                }


                if ($result[$i]['statut'] == "" || $result[$i]['statut'] == null) {
                    $statutDetail = "";
                } else {
                    $statutDetail = $result[$i]['statut'];
                }
                if ($result[$i]['StatutCIS'] == "" || $result[$i]['StatutCIS'] == null) {
                    $statutCisDetail = "";
                } else {
                    $statutCisDetail = $result[$i]['StatutCIS'];
                }
                if ($result[$i]['datestatut'] == "" || $result[$i]['datestatut'] == null) {
                    $datestatutDetail = "";
                } else {
                    $datestatutDetail = (new DateTime($result[$i]['datestatut']))->format('d/m/Y');
                }
                if ($result[$i]['DateStatutCIS'] == "" || $result[$i]['DateStatutCIS'] == null) {
                    $datestatutCisDetail = "";
                } else {
                    $datestatutCisDetail = (new DateTime($result[$i]['DateStatutCIS']))->format('d/m/Y');
                }
                if ($result[$i]['Eta_ivato'] == "" || $result[$i]['Eta_ivato'] == null) {
                    $dateEtaIvato = "";
                } else {
                    $dateEtaIvato = (new DateTime($result[$i]['Eta_ivato']))->format('d/m/Y');
                }
                if ($result[$i]['Eta_magasin'] == "" || $result[$i]['Eta_magasin'] == null) {
                    $dateEtaMag = "";
                } else {
                    $dateEtaMag = (new DateTime($result[$i]['Eta_magasin']))->format('d/m/Y');
                }
                $row = [
                    'agenceServiceTravaux' => $result[$i]['libsuc'] . ' - ' . $result[$i]['libserv'],
                    'Marque' => $result[$i]['markmat'],
                    'Modele' => $result[$i]['typemat'],
                    'Id' => $result[$i]['idmat'],
                    'N_Serie' => $result[$i]['numserie'],
                    'parc' => $result[$i]['numparc'],
                    'casier' => $result[$i]['casier'],
                    'commentaire' => $result[$i]['commentaire'],
                    'numor_itv' => $result[$i]['numor'] . '-' . $result[$i]['itv'],
                    'dateplanning' => $result[$i]['dateplanning'] == "" ? null : (new DateTime($result[$i]['dateplanning'])),
                    'cst' => $result[$i]['cst'],
                    'ref' => $result[$i]['ref'],
                    'desi' => $result[$i]['desi'],
                    'qteres_or' => $result[$i]['qteres_or'] == 0 ? '' : $result[$i]['qteres_or'],
                    'qteall_or' => $result[$i]['qteall'] == 0 ? '' : $result[$i]['qteall'],
                    'qtereliquat' => $result[$i]['qtereliquat'] == 0 ? '' : $result[$i]['qtereliquat'],
                    'qteliv_or' => $result[$i]['qteliv'] == 0 ? '' : $result[$i]['qteliv'],
                    'statutOR' => $statutDetail,
                    'datestatutOR' => $datestatutDetail,
                    'ctr_marque' => $result[$i]['numcde_cis'] == 0 ? '' : $result[$i]['numcde_cis'],
                    'numerocmd' => $result[$i]['numerocdecis'],
                    'statut_ctrmq' => $result[$i]['statut_ctrmq'] . $result[$i]['statut_ctrmq_cis'],
                    'numcis' => $result[$i]['numcisOR'] == 0 ? '' : $result[$i]['numcisOR'],
                    'qteORlig_cis' => $result[$i]['qteORlig'] == 0 ? '' : $result[$i]['qteORlig'],
                    'qtealllig_cis' => $result[$i]['qtealllig'] == 0 ? '' : $result[$i]['qtealllig'],
                    'qterlqlig_cis' => $result[$i]['qterlqlig'] == 0 ? '' : $result[$i]['qterlqlig'],
                    'qtelivlig_cis' => $result[$i]['qtelivlig'] == 0 ? '' : $result[$i]['qtelivlig'],
                    'statutCis' => $statutCisDetail,
                    'datestatutCis' => $datestatutCisDetail,
                    'Eta_ivato' =>  $dateEtaIvato == '01/01/1900' ? '' : $dateEtaIvato,
                    'Eta_magasin' => $dateEtaMag == '01/01/1900' ? '' : $dateEtaMag,
                    'message' => $result[$i]['message'],
                    'ord' => $result[$i]['Ord'], //*****
                    'status_b' => $result[$i]['status_b'],
                    'Qte_Solde' => $result[$i]['qteSlode'],
                    'qte' => $result[$i]['qte'],
                    'backorder' => $result[$i]['backOrder']
                ];

                $row_excel = $row;
                $row_excel['backorder'] = ''; // Supprimer la partie visuelle Excel
                $row_excel['ord'] = $row_excel['ord'] !== '' ? 'oui' : ''; // Excel

                $data[] = $row;
                $data_excel[] = $row_excel;
            }
        }
        return ['data' => $data, 'data_excel' => $data_excel];
    }
}
