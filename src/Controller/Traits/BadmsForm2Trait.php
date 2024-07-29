<?php

namespace App\Controller\Traits;

use App\Entity\Agence;
use App\Entity\Badm;
use App\Entity\Service;
use App\Entity\CasierValider;
use App\Service\fusionPdf\FusionPdf;

trait BadmsForm2Trait
{
    private function changeEtatAchat($dataEtatAchat)
    {
        if ($dataEtatAchat === 'N') {
            return 'NEUF';
        } else {
            return 'OCCASION';
        }
    }

    private function alertRedirection(string $message, string $chemin = "/Hffintranet/formBadm")
    {
        echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
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
       ->setIdMateriel((int)$data[0]["num_matricule"])
       ->setAnneeDuModele($data[0]["annee"])
       ->setDateAchat($this->formatageDate($data[0]["date_achat"]))
       //etat machine
       ->setHeureMachine((int)$data[0]['heure'])
       ->setKmMachine((int)$data[0]['km'])
       //Agence - service - casier Emetteur
       ;
       $idTypeMouvement = $badm->getTypeMouvement()->getId();
       $agenceEmetteur = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
       $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
       $serviceEmetteur = $em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
       if($idTypeMouvement === 1){
            
    } else {
           $badm->setServiceEmetteur($serviceEmetteur->getCodeService(). ' ' . $serviceEmetteur->getLibelleService());

       }
       $badm->setCasierEmetteur($data[0]["casier_emetteur"])
       ;
       //Agence - service - casier destinataire
       
       if( $idTypeMouvement === 1) {
        $agencedestinataire = null;
         $serviceEmetteur = null;
        $casierDestinataire = null;
        $dateMiseLocation = null;
        $serviceEmetteure = $em->getRepository(Service::class)->find(2);
        
       } elseif ($idTypeMouvement === 2) {
        $agencedestinataire = null;
        $serviceEmetteur = null;
       $casierDestinataire = null;
       $serviceEmetteure = $em->getRepository(Service::class)->find(2);
       $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       } elseif ($idTypeMouvement === 3) {
            if(in_array($agenceEmetteur->getId(), [9, 10, 11])) {
                $agencedestinataire = $em->getRepository(Agence::class)->find(9);
                $serviceEmetteur = $em->getRepository(Service::class)->find(2);
               
            } else {
                $agencedestinataire = $em->getRepository(Agence::class)->find(1);
                $serviceEmetteur = $em->getRepository(Service::class)->find(2);
                
            }
        $casierDestinataire = null;
        $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       } elseif ($idTypeMouvement === 4) {
        $agencedestinataire = $em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $casierDestinataire = $em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
        $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       } elseif($idTypeMouvement === 5) {
        $agencedestinataire = $em->getRepository(Agence::class)->find($agenceEmetteur->getId());
        $casierDestinataire = $em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
        $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
       }
       $badm->setAgence($agencedestinataire);
        $badm->setService($serviceEmetteur);
        $badm->setCasierDestinataire($casierDestinataire);
        $badm->setDateMiseLocation($dateMiseLocation);
       
        //ENTREE EN PARC
        $badm->setEtatAchat($this->changeEtatAchat($data[0]["mmat_nouo"]));
        
        //BILAN FINANCIERE
        $badm->setCoutAcquisition((float)$data[0]["droits_taxe"])
        ->setAmortissement((float)$data[0]["amortissement"])
        ->setValeurNetComptable((float)$data[0]["droits_taxe"] - $data[0]["amortissement"])
        
        //date de demande
       ->setDateDemande(new \DateTime())
       ;

       return $badm;
    }

/**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     * @param [type] $form
     * @param [type] $dits
     * @param [type] $nomFichier
     * @return void
     */
    private function uplodeFile($form, $badm, $nomFichier)
    {
        
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = $badm->getNumBadm() . '.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/bdm/fichiers/';
        //$fileDossier = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\PRODUCTION\\DIT\\';
        $file->move($fileDossier, $fileName);


        $setPieceJoint = 'set'.ucfirst($nomFichier);
        $badm->$setPieceJoint($fileName);

        
    }

    private function envoiePieceJoint($form, $badm)
    {
        
        if($form->get("nomImage")->getData() !== null){
                $this->uplodeFile($form, $badm, "nomImage");
            }

            if($form->get("nomFichier")->getData() !== null){
                $this->uplodeFile($form, $badm, "nomFichier");
            }
        
    }

    

}