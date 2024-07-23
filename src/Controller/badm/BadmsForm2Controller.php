<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Entity\Agence;
use App\Form\BadmForm2Type;
use App\Controller\Controller;
use App\Controller\Traits\BadmsForm2Trait;
use App\Controller\Traits\FormatageTrait;
use App\Entity\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BadmsForm2Controller extends Controller
{
    use FormatageTrait;
    use BadmsForm2Trait;

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

        //dump($form1Data);
        $data = $this->badm->findAll($form1Data['idMateriel'],  $form1Data['numParc'], $form1Data['numSerie']);
        //dd($data);
       /** INITIALISATION */
       $badm
       ->setTypeMouvement($form1Data['typeMouvemnt'])
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
       //Agence - service - casier Emetteur
       ;
       $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
       $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
       $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
       $badm->setServiceEmetteur($serviceEmetteur->getCodeService(). ' ' . $serviceEmetteur->getLibelleService())
       ->setCasierEmetteur($data[0]["casier_emetteur"])
       ;
       //Agence - service - casier destinataire
       $idTypeMouvement = $badm->getTypeMouvement()->getId();
       if( $idTypeMouvement === 1 || $idTypeMouvement === 2) {
        $badm->setAgence(null);
        $badm->setService(null);
        $badm->setCasierDestinataire(null);
       } else {
        $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $badm->setAgence($agencedestinataire);
        $badm->setService($serviceEmetteur);
        $badm->setCasierDestinataire($agencedestinataire->getCasiers()->first());
       }
       
        //ENTREE EN PARC
        $badm->setEtatAchat($this->changeEtatAchat($data[0]["mmat_nouo"]))
        ->setDateMiseLocation(\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]))
        //BILAN FINANCIERE
        ->setCoutAcquisition($data[0]["prix_achat"])
        ->setAmortissement($data[0]["amortissement"])
        ->setValeurNetComptable($data[0]["prix_achat"] - $data[0]["amortissement"])
        
        //date de demande
       ->setDateDemande(new \DateTime())
       ;
       //dd($badm);
       $form = self::$validator->createBuilder(BadmForm2Type::class, $badm)->getForm();
        self::$twig->display(
            'badm/secondForm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'items' => $data,
                'form1Data' => $form1Data,
                'form' => $form->createView()
            ]
        );
    }
}