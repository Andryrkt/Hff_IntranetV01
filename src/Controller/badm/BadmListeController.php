<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Form\BadmSearchType;
use App\Entity\TypeMouvement;
use App\Controller\Controller;
use App\Entity\StatutDemande;
use App\Form\ExcelExportType;
use App\Service\ExcelExporterService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class BadmListeController extends Controller
{
    

    /**
     * @Route("/listBadm", name="badmListe_AffichageListeBadm")
     */
    public function AffichageListeBadm(Request $request)
    {
        $this->SessionStart();

        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


       
        if($request->query->get('page') !== null){
            if($request->query->get('typeMouvement') !==null){
                $idTypeMouvement = self::$em->getRepository(TypeMouvement::class)->findBy(['description' => $request->query->get('typeMouvement')], [])[0]->getId();
                $typeMouvement = self::$em->getRepository(TypeMouvement::class)->find($idTypeMouvement) ;
            } else {
                $typeMouvement = $request->query->get('typeMouvement', null);
            }

            if($request->query->get('statut') !==null){
                $idStatut = self::$em->getRepository(StatutDemande::class)->findBy(['description' => $request->query->get('statut')], [])[0]->getId();
                $statut = self::$em->getRepository(StatutDemande::class)->find($idStatut) ;
            } else {
                $statut = $request->query->get('statut', null);
            }
            
        } else {
            $typeMouvement = $request->query->get('typeMouvement', null);
            $statut = $request->query->get('statut', null);
        }
        
        if($request->query->get('badm_search') !== null) {
            if($request->query->get('badm_search')['typeMouvement'] !== null){
                $idTypeMouvement = $request->query->get('badm_search')['typeMouvement'];
                $typeMouvement = self::$em->getRepository(TypeMouvement::class)->find($idTypeMouvement);
            } else {
                $typeMouvement = $request->query->get('typeMouvement', null);
            }

            if($request->query->get('badm_search')['statut'] !== null){
                $idStatut = $request->query->get('badm_search')['statut'];
                $statut = self::$em->getRepository(StatutDemande::class)->find($idStatut);
            } else {
                $statut = $request->query->get('statut', null);
            }
            
        } else {
            $typeMouvement = $request->query->get('typeMouvement', null);
            $statut = $request->query->get('statut', null);
        }
    
        $criteria = [
            'statut' => $statut,
            'typeMouvement' => $typeMouvement,
            'idMateriel'=> $request->query->get('idMateriel'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin')
        ];

        // dd($request->query->get('badm_search')['typeMouvement']);
        //$typeMouvements = $this->badmRech->recupTypeMouvement();
//  dd($criteria);
       
       
        $form = self::$validator->createBuilder(BadmSearchType::class, $criteria, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();

            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                if(!empty($idMateriel)){
                    $criteria['statut'] = $form->get('statut')->getData();
                    $criteria['typeMouvement'] = $form->get('typeMouvement')->getData();
                    $criteria['idMateriel'] = $idMateriel[0]['num_matricule'];
                    $criteria['dateDebut'] = $form->get('dateDebut')->getData();
                    $criteria['dateFin'] = $form->get('dateFin')->getData();
                }
            } else {

                $criteria['statut'] = $form->get('statut')->getData();
                $criteria['typeMouvement'] = $form->get('typeMouvement')->getData();
                $criteria['idMateriel'] = $form->get('idMateriel')->getData();
                $criteria['dateDebut'] = $form->get('dateDebut')->getData();
                $criteria['dateFin'] = $form->get('dateFin')->getData();
            }
        } 

        
        
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository= self::$em->getRepository(Badm::class);
        $data = $repository->findPaginatedAndFiltered($page, $limit, $criteria);
        $totalBadms = $repository->countFiltered($criteria);

        $totalPages = ceil($totalBadms / $limit);
        
        if($request->query->get("envoyer") === "excelBadm") {
         $this->excelExport->exportToExcelBadm($repository->findAndFilteredExcel($criteria));
        }

        if($request->query->get("envoyer") === "listAnnuler") {
        
            $data = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria);
            $totalBadms = $repository->countFilteredListAnnuller($criteria);

            $totalPages = ceil($totalBadms / $limit);
        }
        


        self::$twig->display(
            'badm/listBadm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView(),
                // 'pagination' => $pagination
                // 'typeMouvement' => $typeMouvement,
                'data' => $data,
                'currentPage' => $page,
                'totalPages' =>$totalPages,
                'criteria' => $criteria,
               'resultat' => $totalBadms,
               
               
            ]
        );
    }

  
    // /**
    //  * @Route("/ListJsonBadm")
    //  */
    // public function envoiListJsonBadm()
    // {
    //     $this->SessionStart();

    //     $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    //     $text = file_get_contents($fichier);
    //     $boolean = strpos($text, $_SESSION['user']);

    //     if ($boolean) {
    //         $badmJson = $this->badmRech->RechercheBadmModelAll();
    //     } else {
    //         $badmJson = $this->badmRech->RechercheBadmMode($_SESSION['user']);
    //     }


    //     header("Content-type:application/json");

    //     $jsonData = json_encode($badmJson);


    //     $this->testJson($jsonData);
    // }


    // private function testJson($jsonData)
    // {
    //     if ($jsonData === false) {
    //         // L'encodage a échoué, vérifions pourquoi
    //         switch (json_last_error()) {
    //             case JSON_ERROR_NONE:
    //                 echo 'Aucune erreur';
    //                 break;
    //             case JSON_ERROR_DEPTH:
    //                 echo 'Profondeur maximale atteinte';
    //                 break;
    //             case JSON_ERROR_STATE_MISMATCH:
    //                 echo 'Inadéquation des états ou mode invalide';
    //                 break;
    //             case JSON_ERROR_CTRL_CHAR:
    //                 echo 'Caractère de contrôle inattendu trouvé';
    //                 break;
    //             case JSON_ERROR_SYNTAX:
    //                 echo 'Erreur de syntaxe, JSON malformé';
    //                 break;
    //             case JSON_ERROR_UTF8:
    //                 echo 'Caractères UTF-8 malformés, possiblement mal encodés';
    //                 break;
    //             default:
    //                 echo 'Erreur inconnue';
    //                 break;
    //         }
    //     } else {
    //         // L'encodage a réussi
    //         echo $jsonData;
    //     }
    // }
}
