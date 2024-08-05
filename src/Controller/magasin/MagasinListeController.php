<?php

namespace App\Controller\magasin;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use App\Entity\DemandeIntervention;
use App\Form\MagasinListOrSearchType;
use App\Form\MagasinSearchType;
use App\Model\magasin\MagasinListeOrModel;
use App\Model\magasin\MagasinModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MagasinListeController extends Controller
{
    use Transformation;
    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $magasinModel = new MagasinModel();
        $empty = false;

        $form = self::$validator->createBuilder(MagasinSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
           
            // if ($criteria['niveauUrgence'] === null){
            //     $criteria = [];
            // }
        } 


            $numOrValide = $this->transformEnSeulTableau($magasinModel->recupNumOr($criteria));

            $numOrValideString = implode(',', $numOrValide);

            $data = $magasinModel->recupereListeMaterielValider($numOrValideString, $criteria);
            
            //ajouter le numero dit dans data
            for ($i=0; $i < count($data) ; $i++) { 
                $numeroOr = $data[$i]['numeroor'];
                $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
                if( !empty($dit)){
                    $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
                    $data[$i]['niveauUrgence'] = $dit[0]['description'];
                } else {
                    $empty = true;
                    break;
                }
            }
       

        
        if(empty($data)  ){
            $empty = true;
        }
       
        self::$twig->display('magasin/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data,
            'empty' => $empty,
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/liste-or", name="liste_or")
     *
     * @return void
     */
    public function listOr(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $magasinModel = new MagasinListeOrModel;
        $empty = false;

        $form = self::$validator->createBuilder(MagasinListOrSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
           
            // if ($criteria['niveauUrgence'] === null){
            //     $criteria = [];
            // }
        } 
        
            $numOrValide = $this->transformEnSeulTableau($magasinModel->recupNumOr($criteria));

            $numOrValideString = implode(',', $numOrValide);
            
            $data = $magasinModel->recupereListeMaterielValider($numOrValideString, $criteria);
            
            //ajouter le numero dit dans data
            for ($i=0; $i < count($data) ; $i++) { 
                $numeroOr = $data[$i]['numeroor'];
                $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
                if( !empty($dit)){
                    $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
                    $data[$i]['niveauUrgence'] = $dit[0]['description'];
                } else {
                    $empty = true;
                    break;
                }
            }
       

        
        if(empty($data)  ){
            $empty = true;
        }
       
        self::$twig->display('magasin/listOr.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data,
            'empty' => $empty,
            'form' => $form->createView()
        ]);
    }
}