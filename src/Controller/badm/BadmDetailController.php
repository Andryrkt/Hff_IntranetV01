<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Entity\Badm;
use Symfony\Component\Routing\Annotation\Route;


class BadmDetailController extends Controller
{


    private function rendreSeultableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as $values) {
            foreach ($values as $value) {
                $tab[] = $value;
            }
        }
        return $tab;
    }

    /**
     * @Route("/detailBadm/{numBadm}/{id}", name="BadmDetail_detailBadm")
     */
    public function detailBadm($numBadm, $id)
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
      

        $badm = self::$em->getRepository(Badm::class)->findOneBy(['id' => $id]);
        

        $data = $this->badmDetail->findAll($badm->getIdMateriel());
    
       
 
      
        self::$twig->display(
            'badm/detail.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'badm' => $badm,
                'data' => $data
            ]
        );
    }
}
