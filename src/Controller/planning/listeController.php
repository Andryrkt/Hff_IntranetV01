<?php

namespace App\Controller\planning;

use App\Controller\Controller;
use App\Model\planning\PlanningModel;
use App\Service\TableauEnStringService;
use App\Controller\Traits\PlanningTraits;
use App\Controller\Traits\Transformation;
use App\Entity\planning\PlanningSearch;
use Symfony\Component\HttpFoundation\Request;
use App\Form\planning\PlanningSearchType;
use DateTime;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;

class ListeController extends Controller
{
    use Transformation;
    use PlanningTraits;
    private PlanningSearch $planningSearch;
    private PlanningModel $planningModel;
    public function __construct()
    {
        parent::__construct();
        $this->planningSearch = new PlanningSearch();
        $this->planningModel = new PlanningModel();
    }
    /**
     * @Route("/Liste",name = "liste_planning")
     * 
     *@return void
     */
    public function listecomplet(Request $request)
    {
        $resultat = 0;
        $pagesCount = 0;
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        //initialisation

        $this->conditionFormulaireRecherche();

        $form = self::$validator->createBuilder(
            PlanningSearchType::class,
            $this->planningSearch,
            [
                'method' => 'GET'
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
        $this->sessionService->set('planning_search_criteria', $criteriaTAb);

        // Récupère la page actuelle depuis la requête (par défaut : 1)
        $page = $request->query->getInt('page', 1);
        $limit = 20; // Nombre d'éléments par page

        $data = [];
        if ($request->query->get('action') !== 'oui') {

            $lesOrvalides = $this->recupNumOrValider($criteria, self::$em);
            // dump($lesOrvalides['orSansItv']);
            $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv']);

            if (is_array($back)) {
                $backString = TableauEnStringService::orEnString($back);
            } else {
                $backString = '';
            }

            $res1 = $this->planningModel->recuperationMaterielplanifierListe($criteria, $lesOrvalides['orSansItv'], $backString, $page, $limit);
             $resultat = $this->planningModel->recuperationNombreMaterielplanifier($criteria, $lesOrvalides['orSansItv'], $backString);

            // Calcule le nombre total de pages
            $pagesCount = ceil($resultat / $limit);

            $data = $this->recuperationDonnees($res1, $criteriaTAb);
            // dd($data);
        }
        // dump($data);
        self::$twig->display('planning/listePlanning.html.twig', [
            'form' => $form->createView(),
            'currentPage' => $page,
            'totalPages' => $pagesCount,
            'resultat' => $resultat,
            'criteria' => $criteriaTAb,
            'data' => $data,
        ]);
    }

    /**
     * @Route("/export_excel_liste_planning", name= "export_liste_planning")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteriaTAb = $this->sessionService->get('planning_search_criteria');

        $criteria = $this->creationObjetCriteria($criteriaTAb);
        $lesOrvalides = $this->recupNumOrValider($criteria, self::$em);

        $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv']);

        if (is_array($back)) {
            $backString = TableauEnStringService::orEnString($back);
        } else {
            $backString = '';
        }

        $res1 = $this->planningModel->recuperationMaterielplanifierListe($criteria, $lesOrvalides['orSansItv'], $backString, 1, 0, true);

        $data = $this->recuperationDonnees($res1, $criteriaTAb, true);

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
            'numcis' => 'Num CIS',
            'numerocmd' => 'Numéro CMD',
            'statut_ctrmq' => 'Statut CTRMQ',
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

        $criteria = $this->sessionService->get('planning_search_criteria');

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

    private function recuperationDonnees($res1, $criteriaTAb, $sendCmd = false)
    {
        $data = [];
        if (!empty($res1)) {
            for ($i = 0; $i < count($res1); $i++) {
                // $details = $this->planningModel->recuperationDetailPieceInformix($res1[$i]['orintv'], $criteriaTAb);
                $details = $this->planningModel->recuperationDetailPieceInformixListe($res1[$i]['numor'], $criteriaTAb, $res1[$i]['itv']);
                $qteCis = [];
                $dateLivLigCIS = [];
                $dateAllLigCIS = [];
                for ($j = 0; $j < count($details); $j++) {


                    if (substr($details[$j]['numor'], 0, 1) == '5') {
                        if ($details[$j]['numcis'] !== "0" || $details[$j]['numerocdecis'] == "0") {
                            $recupGcot = [];
                            $qteCis[] = $this->planningModel->recupeQteCISlig($details[$j]['numor'], $details[$j]['intv'], $details[$j]['ref']);
                            $dateLivLigCIS[] = $this->planningModel->dateLivraisonCIS($details[$j]['numcis'], $details[$j]['ref'], $details[$j]['cst']);
                            $dateAllLigCIS[] = $this->planningModel->dateAllocationCIS($details[$j]['numcis'], $details[$j]['ref'], $details[$j]['cst']);
                            $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($details[$j]['numerocdecis']);
                        } else {
                            $etatMag[] = $this->planningModel->recuperationEtaMag($details[$j]['numerocdecis'], $details[$j]['ref'], $details[$j]['cst']);
                            $qteCis[] = $this->planningModel->recupeQteCISlig($details[$j]['numor'], $details[$j]['intv'], $details[$j]['ref']);
                            $dateLivLigCIS[] = $this->planningModel->dateLivraisonCIS($details[$j]['numcis'], $details[$j]['ref'], $details[$j]['cst']);
                            $dateAllLigCIS[] = $this->planningModel->dateAllocationCIS($details[$j]['numcis'], $details[$j]['ref'], $details[$j]['cst']);
                            $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($details[$j]['numerocdecis']);
                            $recupPartiel[] = $this->planningModel->recuperationPartiel($details[$j]['numerocdecis'], $details[$j]['ref']);
                        }
                    } else {
                        if (empty($details[$j]['numerocmd']) || $details[$j]['numerocmd'] == '0') {
                            $recupGcot = [];
                        } else {
                            $recupPartiel[] = $this->planningModel->recuperationPartiel($details[$j]['numerocmd'], $details[$j]['ref']);
                            $etatMag[] = $this->planningModel->recuperationEtaMag($details[$j]['numerocmd'], $details[$j]['ref'], $details[$j]['cst']);
                            $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($details[$j]['numerocmd']);
                        }
                    }

                    if (!empty($etatMag[0])) {
                        $details[$j]['Eta_ivato'] = $etatMag[0][0]['Eta_ivato'];
                        $details[$j]['Eta_magasin'] =  $etatMag[0][0]['Eta_magasin'];
                        $etatMag = [];
                    } else {
                        $details[$j]['Eta_ivato'] = "";
                        $details[$j]['Eta_magasin'] = "";
                        $etatMag = [];
                    }

                    if (!empty($recupPartiel[$j])) {
                        $details[$j]['qteSlode'] = $recupPartiel[$j]['0']['solde'];
                        $details[$j]['qte'] = $recupPartiel[$j]['0']['qte'];
                    } else {
                        $details[$j]['qteSlode'] = "";
                        $details[$j]['qte'] = "";
                    }

                    if (!empty($recupGcot)) {
                        $details[$j]['Ord'] = $recupGcot['ord'] === false ? '' : ($sendCmd === false ? $recupGcot['ord']['Ord'] : "oui");
                    } else {
                        $details[$j]['Ord'] = "";
                    }
                    if (!empty($dateLivLigCIS[$j][0])) {
                        $details[$j]['dateLivLIg'] = $dateLivLigCIS[$j]['0']['datelivlig'];
                    } else {
                        $details[$j]['dateLivLIg'] = "";
                    }

                    if (!empty($dateAllLigCIS[0])) {
                        $details[$j]['dateAllLIg'] = $dateAllLigCIS[$j]['0']['datealllig'];
                    } else {
                        $details[$j]['dateAllLIg'] = "";
                    }

                    if (!empty($qteCis)) {
                        if (!empty($qteCis[$j])) {
                            $details[$j]['qteORlig'] = $qteCis[$j]['0']['qteorlig'];
                            $details[$j]['qtealllig'] = $qteCis[$j]['0']['qtealllig'];
                            $details[$j]['qterlqlig'] = $qteCis[$j]['0']['qtereliquatlig'];
                            $details[$j]['qtelivlig'] = $qteCis[$j]['0']['qtelivlig'];
                        } elseif (isset($qteCis[$j - 1]) && !empty($qteCis[$j - 1])) {
                            $details[$j]['qteORlig'] = $qteCis[$j - 1]['0']['qteorlig'];
                            $details[$j]['qtealllig'] = $qteCis[$j - 1]['0']['qtealllig'];
                            $details[$j]['qterlqlig'] = $qteCis[$j - 1]['0']['qtereliquatlig'];
                            $details[$j]['qtelivlig'] = $qteCis[$j - 1]['0']['qtelivlig'];
                        } else {
                            $details[$j]['qteORlig'] = "";
                            $details[$j]['qtealllig'] = "";
                            $details[$j]['qterlqlig'] = "";
                            $details[$j]['qtelivlig'] = "";
                        }
                    } else {
                        $details[$j]['qteORlig'] = "";
                        $details[$j]['qtealllig'] = "";
                        $details[$j]['qterlqlig'] = "";
                        $details[$j]['qtelivlig'] = "";
                    }

                    if ($details[$j]['qtelivlig'] > 0 &&  $details[$j]['qtealllig']  == 0 && $details[$j]['qterlqlig'] == 0) {
                        $details[$j]['StatutCIS'] = "LIVRE";
                        $details[$j]['DateStatutCIS'] = $details[$j]['dateLivLIg'];
                    } elseif ($details[$j]['qtealllig'] > 0) {
                        $details[$j]['StatutCIS'] = "A LIVRER";
                        $details[$j]['DateStatutCIS'] = $details[$j]['dateAllLIg'];
                    } else {
                        $details[$j]['StatutCIS'] = "";
                        $details[$j]['DateStatutCIS'] = "";
                    }
                    if ($details[$j]['numcis'] === $details[$j]['numerocmd']) {
                        $details[$j]['numcde_cis'] = $details[$j]['numcis'];
                    } else {
                        $details[$j]['numcde_cis'] = $details[$j]['numcis'];
                    }
                   

                    if ($details[$j]['statut'] == "" || $details[$j]['statut'] == null  ) {
                        $statutDetail = "";
                    } else {
                        $statutDetail = $details[$j]['statut'];
                    }
                    if ($details[$j]['StatutCIS'] == "" || $details[$j]['StatutCIS'] == null  ) {
                        $statutCisDetail = "";
                    } else {
                        $statutCisDetail = $details[$j]['StatutCIS'];
                    }
                    if ($details[$j]['datestatut'] == "" || $details[$j]['datestatut'] == null  ) {
                        $datestatutDetail = "";
                    } else {
                        $datestatutDetail = (new DateTime($details[$j]['datestatut']))->format('d/m/Y');
                    }
                    if ($details[$j]['DateStatutCIS'] == "" || $details[$j]['DateStatutCIS'] == null  ) {
                        $datestatutCisDetail = "";
                    } else {
                        $datestatutCisDetail = (new DateTime($details[$j]['DateStatutCIS']))->format('d/m/Y');
                    }
                    if ($details[$j]['Eta_ivato'] == "" || $details[$j]['Eta_ivato'] == null  ) {
                        $dateEtaIvato = "";
                    } else {
                        $dateEtaIvato = (new DateTime($details[$j]['Eta_ivato']))->format('d/m/Y');
                    }
                    if ($details[$j]['Eta_magasin'] == "" || $details[$j]['Eta_magasin'] == null  ) {
                        $dateEtaMag = "";
                    } else {
                        $dateEtaMag = (new DateTime($details[$j]['Eta_magasin']))->format('d/m/Y');
                    }
                    $data[] = [
                        'agenceServiceTravaux' => $res1[$i]['libsuc'] . ' - ' . $res1[$i]['libserv'],
                        'Marque' => $res1[$i]['markmat'],
                        'Modele' => $res1[$i]['typemat'],
                        'Id' => $res1[$i]['idmat'],
                        'N_Serie' => $res1[$i]['numserie'],
                        'parc' => $res1[$i]['numparc'],
                        'casier' => $res1[$i]['casier'],
                        'commentaire' => $details[$j]['commentaire'],
                        'numor_itv' => $details[$j]['numor'] . '-' . $details[$j]['intv'],
                        'dateplanning' => $details[$j]['dateplanning'],
                        'cst' => $details[$j]['cst'],
                        'ref' => $details[$j]['ref'],
                        'desi' => $details[$j]['desi'],
                        'qteres_or' => $details[$j]['qteres_or'],
                        'qteall_or' => $details[$j]['qteall'],
                        'qtereliquat' => $details[$j]['qtereliquat'],
                        'qteliv_or' => $details[$j]['qteliv'], /**** */
                        'statutOR' => $statutDetail,
                        'datestatutOR' => $datestatutDetail  ,
                        // 'numcis' => $details[$j]['numcis'].$details[$j]['numerocmd'] ,
                        'numcis' => $details[$j]['numcde_cis'],
                        'numerocmd' => $details[$j]['numerocdecis'],
                        'statut_ctrmq' => $details[$j]['statut_ctrmq'] . $details[$j]['statut_ctrmq_cis'],
                        'qteORlig_cis' => $details[$j]['qteORlig'],
                        'qtealllig_cis' => $details[$j]['qtealllig'],
                        'qterlqlig_cis' => $details[$j]['qterlqlig'],
                        'qtelivlig_cis' => $details[$j]['qtelivlig'],
                        'statutCis' => $statutCisDetail,
                        'datestatutCis' => $datestatutCisDetail ,
                        'Eta_ivato' =>  $dateEtaIvato ,
                        'Eta_magasin' => $dateEtaMag ,
                        'message' => $details[$j]['message'],
                        'ord' => $details[$j]['Ord'],
                        'status_b' =>$res1[$i]['status_b']

                    ];
                }
            }
        }
        return $data;
    }
}
