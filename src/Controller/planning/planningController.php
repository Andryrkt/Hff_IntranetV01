<?php
namespace App\Controller\planning;

use App\Controller\Controller;
use App\Controller\Traits\PlanningTraits;
use App\Model\planning\PlanningModel;

use App\Entity\planning\PlanningSearch;
use App\Controller\Traits\Transformation;
use App\Entity\dit\DemandeIntervention;
use App\Entity\planning\PlanningMateriel;
use App\Form\planning\PlanningSearchType;
use App\Service\fusionPdf\FusionPdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends Controller
{        
    use Transformation; 
    use PlanningTraits;
        private PlanningModel $planningModel;
        
        public function __construct()
        {
            parent::__construct();
            $this->planningModel = new PlanningModel();
        }

        /**
         * @Route("/planning", name="planning_vue")
         * 
         * @return void
         */
        public function listePlanning( Request $request)
        {
            //verification si user connecter
            $this->verifierSessionUtilisateur();

            $planningSearch = new PlanningSearch();
            //initialisation
            $planningSearch
                ->setAnnee(date('Y'))
                ->setFacture('ENCOURS')
                ->setPlan('PLANIFIE')
                ->setInterneExterne('TOUS')
                ->setTypeLigne('TOUETS')
            ;

            $form = self::$validator->createBuilder(PlanningSearchType::class,$planningSearch,
            [ 
                'method' =>'GET'
            ])->getForm();

            $form->handleRequest($request);
            $criteria = $planningSearch;
            if($form->isSubmitted() && $form->isValid())
            {
                  // dd($form->getdata());
                $criteria =  $form->getdata();

            }
            $criteriaTAb = [];
            //transformer l'objet ditSearch en tableau
            $criteriaTAb = $criteria->toArray();
            // dump($criteriaTAb);
            //recupères les données du criteria dans une session nommé dit_serch_criteria
            $this->sessionService->set('planning_search_criteria', $criteriaTAb);

            
            if($request->query->get('action') !== 'oui') 
            {
                $lesOrvalides = $this->recupNumOrValider($criteria, self::$em);

                $data = $this->planningModel->recuperationMaterielplanifier($criteria,$lesOrvalides);

            } else {
                $data = [];
            }
            
            $table = [];
            //Recuperation de idmat et les truc
            foreach ($data as $item ) {
                $planningMateriel = new PlanningMateriel();
                $numDit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $item['orintv'])[0]])->getNumeroDemandeIntervention();
                  //initialisation
                    $planningMateriel
                        ->setCodeSuc($item['codesuc'])
                        ->setLibSuc($item['libsuc'])
                        ->setCodeServ($item['codeserv'])
                        ->setLibServ($item['libserv'])
                        ->setIdMat($item['idmat'])
                        ->setMarqueMat($item['markmat'])
                        ->setTypeMat($item['typemat'])
                        ->setNumSerie($item['numserie'])
                        ->setNumParc($item['numparc'])
                        ->setCasier($item['casier'])
                        ->setAnnee($item['annee'])
                        ->setMois($item['mois'])
                        ->setOrIntv($item['orintv'])
                        ->setQteCdm($item['qtecdm'])
                        ->setQteLiv($item['qtliv'])
                        ->setQteAll($item['qteall'])
                        ->setNumDit($numDit)
                        ->addMoisDetail($item['mois'], $item['orintv'], $item['qtecdm'], $item['qtliv'], $item['qteall'], $numDit)
                    ;
                    $table[] = $planningMateriel;
            }


            // Fusionner les objets en fonction de l'idMat

            $fusionResult = [];
            foreach ($table as $materiel) {
                $key = $materiel->getIdMat(); // Utiliser idMat comme clé unique

                if (!isset($fusionResult[$key])) {
                    $fusionResult[$key] = $materiel; // Si la clé n'existe pas, on l'ajoute
                } else {
                    // Si l'élément existe déjà, on fusionne les détails des mois
                    foreach ($materiel->moisDetails as $moisDetail) {

                        $fusionResult[$key]->addMoisDetail(
                            $moisDetail['mois'],
                            $moisDetail['orIntv'],
                            $moisDetail['qteCdm'],
                            $moisDetail['qteLiv'],
                            $moisDetail['qteAll'],
                            $moisDetail['numDit']
                        );
                    }
                    
                }
            }

            //dump($fusionResult);
            self::$twig->display('planning/planning.html.twig', [
                'form' => $form->createView(),
                'data' => $fusionResult
            ]);
        }


    /**
     * @Route("/serviceDebiteurPlanning-fetch/{agenceId}")
     */
    public function serviceDebiteur($agenceId)
    {
        $serviceDebiteur = $this->planningModel->recuperationServiceDebite($agenceId);
        
        header("Content-type:application/json");

        echo json_encode($serviceDebiteur);
    }
    
    /**
     * @Route("/detail-modal/{numOr}", name="liste_detailModal")
     *
     * @return void
     */
    public function detailModal($numOr)
    {

        $criteria = $this->sessionService->get('planning_search_criteria', []);
    // dd($criteria);
        //RECUPERATION DE LISTE DETAIL 
        if ($numOr === '') {
            $details = [];
        } else {
            $details = $this->planningModel->recuperationDetailPieceInformix($numOr, $criteria);
            //$numDit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $numOr)[0]]);
            $detailes = [];
            $recupPariel = [];
            $recupGot = [];
            for ($i=0; $i < count($details); $i++) {
                if(empty($details[$i]['numerocmd']) || $details[$i]['numerocmd'] == "0" ){
                    $recupGot = [];
                } else {
                    $detailes[]= $this->planningModel->recuperationEtaMag($details[$i]['numor'], $details[$i]['ref']);
                    $recupPariel[] = $this->planningModel->recuperationPartiel($details[$i]['numerocmd'],$details[$i]['ref']);
                    $recupGot['ord']= $this->planningModel->recuperationinfodGcot($details[$i]['numerocmd']);
                }
                
                if(!empty($detailes[0])){
                        $details[$i]['Eta_ivato'] = $detailes[0][0]['Eta_ivato'];
                        $details[$i]['Eta_magasin'] =  $detailes[0][0]['Eta_magasin']; 
                        $detailes = [];                 
                } 
                else {
                    $details[$i]['Eta_ivato'] = "";
                    $details[$i]['Eta_magasin'] = "";  
                    $detailes = [];              
                } 
                
                if(!empty($recupPariel[$i])){
                    $details[$i]['qteSlode'] = $recupPariel[$i]['0']['solde'];
                    $details[$i]['qte'] = $recupPariel[$i]['0']['qte'];
                }else{
                    $details[$i]['qteSlode'] = "";
                    $details[$i]['qte'] = "";
                }
                // dump($recupGot);
                if(!empty($recupGot)){
                    $details[$i]['Ord']= $recupGot['ord'] === false ? '' : $recupGot['ord']['Ord'];
                }else{
                    $details[$i]['Ord'] = "";
                }
            
                 //$detatils[$i]['numDit'] = $numDit;
            }

            
        }

        //dd($details);
        header("Content-type:application/json");

        echo json_encode($details);
    }
}