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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;

class ListeController extends Controller
{
    use Transformation;
    use PlanningTraits;
    private PlanningSearch $PlanningSearch;
    private PlanningModel $planningModel;
    public function __construct()
    {
        parent::__construct();
        $this->PlanningSearch = new PlanningSearch();
        $this->planningModel = new PlanningModel();
    }
    /**
     * @Route("/Liste",name = "liste_planning")
     * 
     *@return void
     */
    public function listecomplet(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        //initialisation
        $this->PlanningSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
        ;

        $form = self::$validator->createBuilder(
            PlanningSearchType::class,
            $this->PlanningSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->PlanningSearch;

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
        
        $data = [];
        if ($request->query->get('action') !== 'oui') {
            $lesOrvalides = $this->recupNumOrValider($criteria, self::$em);
            $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv']);

            if (is_array($back)) {
                $backString = TableauEnStringService::orEnString($back);
            } else {
                $backString = '';
            }
            $res1 = $this->planningModel->recuperationMaterielplanifier($criteria, $lesOrvalides['orAvecItv'], $backString);

            for ($i = 0; $i < 1 ; $i++) {
                $details = $this->planningModel->recuperationDetailPieceInformix($res1[$i]['orintv'], $criteriaTAb);
              
                    for ($j=0; $j < count($details); $j++) { 
                        if (substr($details[$j]['numor'], 0, 1) =='5') {
                            if ($details[$j]['numcis'] !== "0" || $details[$j]['numerocdecis'] == "0" ) {
                            $recupGcot = [];
                            $qteCis [] = $this->planningModel->recupeQteCISlig($details[$j]['numor'],$details[$j]['intv'],$details[$j]['ref']);
                            $dateLivLigCIS[] = $this->planningModel->dateLivraisonCIS($details[$j]['numcis'],$details[$j]['ref'],$details[$j]['cst']);
                            $dateAllLigCIS[] = $this->planningModel->dateAllocationCIS($details[$j]['numcis'],$details[$j]['ref'],$details[$j]['cst']);
                            }else{
                                $etatMag[] = $this->planningModel->recuperationEtaMag($details[$j]['numerocdecis'],$details[$j]['ref'],$details[$j]['cst']);
                                $qteCis [] = $this->planningModel->recupeQteCISlig($details[$j]['numor'],$details[$j]['intv'],$details[$j]['ref']);
                                $dateLivLigCIS[] = $this->planningModel->dateLivraisonCIS($details[$j]['numcis'],$details[$j]['ref'],$details[$j]['cst']);
                                $dateAllLigCIS[] = $this->planningModel->dateAllocationCIS($details[$j]['numcis'],$details[$j]['ref'],$details[$j]['cst']);
                                $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($details[$j]['numerocdecis']);
                                $recupPartiel[] = $this->planningModel->recuperationPartiel($details[$j]['numerocdecis'],$details[$j]['ref']);
                            }
                        } else {
                            if (empty($details[$j]['numerocmd']) || $details[$j]['numerocmd'] == '0') {
                            $recupGcot = [];
                            }else{
                                $recupPartiel[] = $this->planningModel->recuperationPartiel($details[$j]['numerocmd'],$details[$j]['ref']);
                                $etatMag[] = $this->planningModel->recuperationEtaMag($details[$j]['numerocmd'],$details[$j]['ref'],$details[$j]['cst']);
                                $recupGcot['ord'] = $this->planningModel->recuperationinfodGcot($details[$j]['numerocmd']);
                            }
                        }
                
                        if(!empty($etatMag[0])){
                            $details[$j]['Eta_ivato'] = $etatMag[0][0]['Eta_ivato'];
                            $details[$j]['Eta_magasin'] =  $etatMag[0][0]['Eta_magasin']; 
                            $etatMag = [];                 
                        } 
                        else {
                            $details[$j]['Eta_ivato'] = "";
                            $details[$j]['Eta_magasin'] = "";  
                            $etatMag = [];              
                        } 
                        
                        if(!empty($recupPartiel[$j])){
                            $details[$j]['qteSlode'] = $recupPartiel[$j]['0']['solde'];
                            $details[$j]['qte'] = $recupPartiel[$j]['0']['qte'];
                        }else{
                            $details[$j]['qteSlode'] = "";
                            $details[$j]['qte'] = "";
                        }
                        
                        if(!empty($recupGot)){
                            $details[$j]['Ord']= $recupGot['ord'] === false ? '' : $recupGot['ord']['Ord'];
                        }else{
                            $details[$j]['Ord'] = "";
                        }
                    
                        if(!empty($dateLivLigCIS[0])){
                            $details[$j]['dateLivLIg']= $dateLivLigCIS[$j]['0']['datelivlig'];
                        }else{
                            $details[$j]['dateLivLIg'] = "";
                        }

                        if(!empty($dateAllLigCIS[0])){
                            $details[$j]['dateAllLIg']= $dateAllLigCIS[$j]['0']['datealllig'];
                        }else{
                            $details[$j]['dateAllLIg'] = "";
                        }

                        if (!empty($qteCIS)) {
                            if(!empty($qteCIS[$j])) {
                                $details[$j]['qteORlig'] = $qteCIS[$j]['0']['qteorlig'];
                                $details[$j]['qtealllig'] = $qteCIS[$j]['0']['qtealllig'];
                                $details[$j]['qterlqlig'] = $qteCIS[$j]['0']['qtereliquatlig'];
                                $details[$j]['qtelivlig'] = $qteCIS[$j]['0']['qtelivlig'];
                            } elseif(!empty($qteCIS[$j-1])){
                                $details[$j]['qteORlig'] = $qteCIS[$j-1]['0']['qteorlig'];
                                $details[$j]['qtealllig'] = $qteCIS[$j-1]['0']['qtealllig'];
                                $details[$j]['qterlqlig'] = $qteCIS[$j-1]['0']['qtereliquatlig'];
                                $details[$j]['qtelivlig'] = $qteCIS[$j-1]['0']['qtelivlig'];
                            }else{
                                $details[$j]['qteORlig'] = "";
                                $details[$j]['qtealllig'] = "";
                                $details[$j]['qterlqlig'] = "";
                                $details[$j]['qtelivlig'] = "";        
                            }
                        }else{
                            $details[$j]['qteORlig'] = "";
                            $details[$j]['qtealllig'] = "";
                            $details[$j]['qterlqlig'] = "";
                            $details[$j]['qtelivlig'] = "";        
                        }
                        if ($details[$j]['qtelivlig'] > 0 &&  $details[$j]['qtealllig']  === 0 && $details[$j]['qterlqlig']) {
                            $details[$j]['StatutCIS'] = "LIVRE";
                            $details[$j]['DateStatutCIS'] = $details[$j]['dateLivLIg'];
                        }elseif( $details[$j]['qtealllig'] > 0){
                            $details[$j]['StatutCIS'] = "A LIVRER";
                            $details[$j]['DateStatutCIS'] = $details[$j]['dateAllLIg'];     
                        }else{
                            $details[$j]['StatutCIS'] = "";
                            $details[$j]['DateStatutCIS'] = ""; 
                        }

                        $data[$j] = [
                            'agenceServiceTravaux' => $res1[$i]['libsuc'] . ' - ' . $res1[$i]['libserv'],
                            'Marque' => $res1[$i]['markmat'],
                            'Modele' => $res1[$i]['typemat'],
                            'Id' => $res1[$i]['idmat'],
                            'N_Serie' => $res1[$i]['numserie'],
                            'parc' => $res1[$i]['numparc'],
                            'casier' => $res1[$i]['casier'],
                            'commentaire' => $details[$j]['commentaire'],
                            'numor_itv' => $details[$j]['numor'].'-'.$details[$j]['intv'],
                            'dateplanning' => $details[$j]['dateplanning'],
                            'cst' => $details[$j]['cst'],
                            'ref' => $details[$j]['ref'],
                            'desi' => $details[$j]['desi'],
                            'qteres_or' => $details[$j]['qteres_or'],
                            'qteall_or' => $details[$j]['qteall'],
                            'qtereliquat' => $details[$j]['qtereliquat'],
                            'qteliv_or' => $details[$j]['qteliv'],
                            'numcis' => $details[$j]['numcis'].$details[$j]['numerocmd'] ,
                            'numerocmd' => $details[$j]['numerocdecis'],
                            'statut_ctrmq' => $details[$j]['statut_ctrmq'] .$details[$j]['statut_ctrmq_cis'],
                            'qteORlig_cis' => $details[$j]['qteORlig'],
                            'qtealllig_cis' => $details[$j]['qtealllig'],
                            'qterlqlig_cis' => $details[$j]['qterlqlig'],
                            'qtelivlig_cis' => $details[$j]['qtelivlig'],
                            'statut' => $details[$j]['statut'].$details[$j]['StatutCIS'],
                            'datestatut' => $details[$j]['datestatut'].$details[$j]['DateStatutCIS'],
                            'Eta_ivato' => $details[$j]['Eta_ivato'],
                            'Eta_magasin' => $details[$j]['Eta_magasin'],
                            'message' => $details[$j]['message'],
                            'ORD' => $details[$j]['Ord']
                     
                        ];      
                    }
                }
            }
        self::$twig->display('planning/listePlanning.html.twig', [
            'form' => $form->createView(),
            'data' => $data,
        ]);
    }
}
