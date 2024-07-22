<?php

namespace App\Controller\Traits;

use App\Entity\Rmq;
use App\Entity\Site;
use App\Entity\Agence;
use App\Entity\AgenceServiceIrium;
use App\Entity\Service;
use App\Entity\Indemnite;
use App\Entity\Personnel;

trait DomsTrait
{
    private function initialisationSecondForm($form1Data, $em) {

        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);

        $this->dom->setMatricule($form1Data['matricule']);
        $this->dom->setSalarier($form1Data['salarier']);
        $this->dom->setSousTypeDocument($form1Data['sousTypeDocument']);
        $this->dom->setCategorie($form1Data['categorie']);
        if ($form1Data['salarier'] === "TEMPORAIRE") {
            $this->dom->setNom($form1Data['nom']);
            $this->dom->setPrenom($form1Data['prenom']);
            $this->dom->setCin($form1Data['cin']);

            $agenceEmetteur = $CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']);
            $serviceEmetteur = $CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']);
            $codeAgenceEmetteur = $CodeServiceofCours[0]['agence_ips'] ;
            $codeServiceEmetteur = $CodeServiceofCours[0]['service_ips'] ;
        
        } else {
            $personnel = $em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
            $agenceServiceIrium = $em->getRepository(AgenceServiceIrium::class)->findOneBy(['service_sage_paie' => $personnel->getCodeAgenceServiceSage()]);
            dump($agenceServiceIrium);
        
            $this->dom->setNom($personnel->getNom());
            $this->dom->setPrenom($personnel->getPrenoms());
            $agenceEmetteur = $agenceServiceIrium->getAgenceips() . ' ' . strtoupper($agenceServiceIrium->getNomagencei100());
            $serviceEmetteur = $agenceServiceIrium->getServiceips() . ' ' . $agenceServiceIrium->getLibelleserviceips();
            $codeAgenceEmetteur = $agenceServiceIrium->getAgenceips()  ;
            $codeServiceEmetteur =  $agenceServiceIrium->getServiceips();
            dump($agenceEmetteur, $serviceEmetteur);

        }
        /** INITIALISATION AGENCE ET SERVICE Emetteur et Debiteur */
        $this->dom->setAgenceEmetteur($agenceEmetteur);
        $this->dom->setServiceEmetteur($serviceEmetteur);
        $idAgence = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgenceEmetteur])->getId();
        $this->dom->setAgence($em->getRepository(Agence::class)->find($idAgence));
        $this->dom->setService($em->getRepository(Service::class)->findOneBy(['codeService' => $codeServiceEmetteur]));

        //initialisation site
        $sousTypedocument = $form1Data['sousTypeDocument'];
            $catg = $form1Data['categorie'];
            
            if($CodeServiceofCours[0]['agence_ips'] === '50'){
                $rmq = $em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
           } else {
                $rmq = $em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
           }
           $criteria = [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq,
            'categorie' => $catg
            ];

            $indemites = $em->getRepository(Indemnite::class)->findBy($criteria);
            $sites = [];
            foreach ($indemites as $key => $value) {
                $sites[] = $value->getSite()->getId();
            }
            if(in_array(8, $sites)){
                $this->dom->setSite($em->getRepository(Site::class)->find(8));
            } else {
                $this->dom->setSite($em->getRepository(Site::class)->find(1));
            }

    }
}