<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Controller\Controller;
use App\Controller\Traits\BadmsTrait;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BadmsForm2Controller extends Controller
{
    use BadmsTrait;
    use FormatageTrait;

    /**
     * @Route("/badm-form2", name="badms_newForm2")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $badm = new Badm();

        $form1Data = $this->sessionService->get('badmform1Data', []);


        $data = $this->badm->findAll($form1Data['idMateriel'],  $form1Data['numParc'], $form1Data['numSerie']);
        dd($data);
       /** INITIALISATION */
       $badm
       //caracteristique du materiel
       ->setGroupe($data[0]["famille"])
       ->setAffectation($data[0]["affectation"])
       ->setConstructeur($data[0]["constructeur"])
       ->setDesignation($data[0]["designation"])
       ->setModele($data[0]["modele"])
       ->setNumParc($data[0]["num_parc"])
       ->setNumSerie($data[0]["num_serie"])
       ->setIdMateriel($data[0]["num_matricule"])
       ->setAnneeDuModele($data[0]["annee"])
       ->setDateAchat($this->formatageDate($data[0]["date_achat"]))
       //etat machine
       ->setHeureMachine($data[0]['heure'])
       ->setKmMachine($data[0]['km'])
       //Agence - service Emetteur
       //Agence service destinataire
       ->setDateDemande(new \DateTime())
       ;
       
       
        self::$twig->display(
            'badm/formCompleBadm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'items' => $data,
               
            ]
        );
    }
}