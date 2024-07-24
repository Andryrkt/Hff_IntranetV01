<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Entity\Agence;
use App\Entity\Service;
use App\Form\BadmForm2Type;
use App\Entity\CasierValider;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\BadmsForm2Trait;
use App\Entity\StatutDemande;
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

        $data = $this->badm->findAll($form1Data['idMateriel'],  $form1Data['numParc'], $form1Data['numSerie']);

       /** INITIALISATION */
       $badm = $this->initialisation($badm, $form1Data, $data, self::$em);
      
       $form = self::$validator->createBuilder(BadmForm2Type::class, $badm)->getForm();
       
       $form->handleRequest($request);
       

            if($form->isSubmitted() && $form->isValid())
            {
                dump($form->getData());
                $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
       $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
       $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
       $badm->setServiceEmetteur($serviceEmetteur->getCodeService(). ' ' . $serviceEmetteur->getLibelleService())
       ->setCasierEmetteur($data[0]["casier_emetteur"]);
                $idTypeMouvement = $badm->getTypeMouvement()->getId();
       if( $idTypeMouvement === 1) {
        $agencedestinataire = null;
         $serviceEmetteur = null;
        $casierDestinataire = null;
        $dateMiseLocation = null;
        
       } elseif ($idTypeMouvement === 2) {
        $agencedestinataire = null;
        $serviceEmetteur = null;
       $casierDestinataire = null;
       $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       } elseif ($idTypeMouvement === 3) {
            if(in_array($agenceEmetteur->getId(), [9, 10, 11])) {
                $agencedestinataire = self::$em->getRepository(Agence::class)->find(9);
                $serviceEmetteur = self::$em->getRepository(Service::class)->find(2);
            } else {
                $agencedestinataire = self::$em->getRepository(Agence::class)->find(1);
                $serviceEmetteur = self::$em->getRepository(Service::class)->find(2);
            }
        $casierDestinataire = null;
        $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       } elseif ($idTypeMouvement === 4) {
        $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $casierDestinataire = self::$em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
        $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       } elseif($idTypeMouvement === 5) {
        $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $casierDestinataire = self::$em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
        $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       }
                $badm
                ->setNumParc($data[0]["num_parc"])
                ->setHeureMachine((int)$data[0]['heure'])
                ->setKmMachine((int)$data[0]['km'])
                ->setEtatAchat($this->changeEtatAchat($data[0]["mmat_nouo"]))
                ->setCoutAcquisition((float)$data[0]["droits_taxe"])
        ->setAmortissement((float)$data[0]["amortissement"])
        ->setValeurNetComptable((float)$data[0]["droits_taxe"] - $data[0]["amortissement"])
        ->setAgence($agencedestinataire)
        ->setService($serviceEmetteur)
        ->setCasierDestinataire($casierDestinataire)
        ->setDateMiseLocation($dateMiseLocation)
        ->setStatutDemande(self::$em->getRepository(StatutDemande::class)->find(15))
        ;
                dd($badm);
            }
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

     /**
     * @Route("/service-fetch/{id}", name="fetch_service", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service destinataire et casier destiantaireselon l'agence debiteur en ajax
     * @return void
     */
    public function agenceFetch(int $id)
    {
        $agence = self::$em->getRepository(Agence::class)->find($id);
  
        $service = $agence->getServices();

     
         $services = [];
       foreach ($service as $value) {
         $services[] = [
             'value' => $value->getId(),
             'text' => $value->getCodeService() . ' ' . $value->getLibelleService(),
         ];
       }

       header("Content-type:application/json");

        echo json_encode($services);
    }

    /**
     * @Route("/casier-fetch/{id}", name="fetch_casier", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service destinataire l'agence debiteur en ajax
     * @return void
     */
    public function casierFetch(int $id)
    {
        $agence = self::$em->getRepository(Agence::class)->find($id);
  
        $casier = $agence->getCasiers();

         $casiers = [];
       foreach ($casier as $value) {
         $casiers[] = [
             'value' => $value->getId(),
             'text' => $value->getCasier()
         ];
       }
       header("Content-type:application/json");

        echo json_encode($casiers);
    }
}