<?php

namespace App\Controller\magasin;

use App\Controller\Controller;
use App\Entity\DemandeIntervention;
use App\Model\magasin\MagasinModel;
use Symfony\Component\Routing\Annotation\Route;

class MagasinListeController extends Controller
{
    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        
        $magasinModel = new MagasinModel();

        $numOrValide = self::$em->getRepository(DemandeIntervention::class)->findNumOr();
        $numOrValideString = implode(',', $numOrValide);
        
        $data = $magasinModel->recupereListeMaterielValider($numOrValideString);
        
        // ajouter le numero dit dans data
        for ($i=0; $i < count($data) ; $i++) { 
            $numeroOr = $data[$i]['numeroor'];
            $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
            $data[$i]['niveauUrgence'] = $dit[0]['description'];
        }

        $empty = false;
        if(empty($data)){
            $empty = true;
        }
       
        self::$twig->display('magasin/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data,
            'empty' => $empty
        ]);
    }
}