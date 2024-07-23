<?php

namespace App\Controller\Traits;

use App\Entity\Agence;
use App\Entity\Badm;
use App\Entity\Service;
use App\Entity\CasierValider;

trait BadmsForm2Trait
{
    private function changeEtatAchat($dataEtatAchat)
    {
        if ($dataEtatAchat === 'N') {
            return 'Neuf';
        } else {
            return 'Occasion';
        }
    }


    private function initialisation(Badm $badm, array $form1Data, $data, $em): Badm
    {
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
       $agenceEmetteur = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
       $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
       $serviceEmetteur = $em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
       $badm->setServiceEmetteur($serviceEmetteur->getCodeService(). ' ' . $serviceEmetteur->getLibelleService())
       ->setCasierEmetteur($data[0]["casier_emetteur"])
       ;
       //Agence - service - casier destinataire
       $idTypeMouvement = $badm->getTypeMouvement()->getId();
       if( $idTypeMouvement === 1) {
        $badm->setAgence(null);
        $badm->setService(null);
        $badm->setCasierDestinataire(null);
        $badm->setDateMiseLocation(null);
       } elseif ($idTypeMouvement === 2) {
        $badm->setAgence(null);
        $badm->setService(null);
        $badm->setCasierDestinataire(null);
        $badm->setDateMiseLocation(\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]));
       } elseif ($idTypeMouvement === 3) {
        if(in_array($agenceEmetteur->getId(), [9, 10, 11])) {
            $agencedestinataire = $em->getRepository(Agence::class)->find(9);
            $serviceEmetteur = $em->getRepository(Service::class)->find(2);
        } else {
            $agencedestinataire = $em->getRepository(Agence::class)->find(1);
            $serviceEmetteur = $em->getRepository(Service::class)->find(2);
        }
        $badm->setAgence($agencedestinataire);
        $badm->setService($serviceEmetteur);
        $badm->setCasierDestinataire(null);
        $badm->setDateMiseLocation(\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]));
       } elseif ($idTypeMouvement === 4) {
        $agencedestinataire = $em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $casierDestinataire = $em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
        $badm->setAgence($agencedestinataire);
        $badm->setService($serviceEmetteur);
        $badm->setCasierDestinataire($casierDestinataire);
        $badm->setDateMiseLocation(\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]));
       } elseif($idTypeMouvement === 5) {
        $agencedestinataire = $em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $casierDestinataire = $em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
        $badm->setAgence($agencedestinataire);
        $badm->setService($serviceEmetteur);
        $badm->setCasierDestinataire($casierDestinataire);
        $badm->setDateMiseLocation(\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]));
       }
       
        //ENTREE EN PARC
        $badm->setEtatAchat($this->changeEtatAchat($data[0]["mmat_nouo"]));
        
        //BILAN FINANCIERE
        $badm->setCoutAcquisition($data[0]["prix_achat"])
        ->setAmortissement($data[0]["amortissement"])
        ->setValeurNetComptable($data[0]["prix_achat"] - $data[0]["amortissement"])
        
        //date de demande
       ->setDateDemande(new \DateTime())
       ;

       return $badm;
    }
}