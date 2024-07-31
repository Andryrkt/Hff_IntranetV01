<?php

namespace App\Controller\magasin;

use App\Controller\Controller;
use App\Entity\DemandeIntervention;
use App\Form\MagasinSearchType;
use App\Model\magasin\MagasinModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MagasinListeController extends Controller
{
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

        $form = self::$validator->createBuilder(MagasinSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            if ($criteria['niveauUrgence'] === null){
                $criteria = [];
            }
        } 
        $magasinModel = new MagasinModel();

        $empty = false;
       

            $numOrValide = self::$em->getRepository(DemandeIntervention::class)->findNumOr($criteria);
            
            $numOrValideString = implode(',', $numOrValide);
            
            $data = $magasinModel->recupereListeMaterielValider($numOrValideString);
            
            // ajouter le numero dit dans data
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
}