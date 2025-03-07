<?php

namespace App\Controller\Traits\dom;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\dom\Rmq;
use App\Entity\admin\Service;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\Personnel;
use App\Entity\admin\Application;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\dom\SousTypeDocument;
use App\Service\genererPdf\GeneratePdfDom;


trait DomsDupliTrait
{
    public function initialisationSecondForm($form1Data, $em, $dom) {

        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);

        $dom->setMatricule($form1Data['matricule']);
        $dom->setSalarier($form1Data['salarier']);
        $dom->setSousTypeDocument($form1Data['sousTypeDocument']);
        $dom->setCategorie($form1Data['categorie']);
        $dom->setDateDemande(new \DateTime());
        if ($form1Data['salarier'] === "TEMPORAIRE") {
            $dom->setNom($form1Data['nom']);
            $dom->setPrenom($form1Data['prenom']);
            $dom->setCin($form1Data['cin']);

            $agenceEmetteur = $CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']);
            $serviceEmetteur = $CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']);
            $codeAgenceEmetteur = $CodeServiceofCours[0]['agence_ips'] ;
            $codeServiceEmetteur = $CodeServiceofCours[0]['service_ips'] ;
        
        } else {
            $personnel = $em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
            $agenceServiceIrium = $em->getRepository(AgenceServiceIrium::class)->findOneBy(['service_sage_paie' => $personnel->getCodeAgenceServiceSage()]);
         
            $dom->setNom($personnel->getNom());
            $dom->setPrenom($personnel->getPrenoms());
            $agenceEmetteur = $agenceServiceIrium->getAgenceips() . ' ' . strtoupper($agenceServiceIrium->getNomagencei100());
            $serviceEmetteur = $agenceServiceIrium->getServiceips() . ' ' . $agenceServiceIrium->getLibelleserviceips();
            $codeAgenceEmetteur = $agenceServiceIrium->getAgenceips()  ;
            $codeServiceEmetteur =  $agenceServiceIrium->getServiceips();

        }
        /** INITIALISATION AGENCE ET SERVICE Emetteur et Debiteur */
        $dom->setAgenceEmetteur($agenceEmetteur);
        $dom->setServiceEmetteur($serviceEmetteur);
        $idAgence = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgenceEmetteur])->getId();
        $dom->setAgence($em->getRepository(Agence::class)->find($idAgence));
        $dom->setService($em->getRepository(Service::class)->findOneBy(['codeService' => $codeServiceEmetteur]));

        //initialisation site
        
            $criteria = $this->criteria($form1Data, $em);
            $indemites = $em->getRepository(Indemnite::class)->findBy($criteria);
            $sites = [];
            foreach ($indemites as $key => $value) {
                $sites[] = $value->getSite()->getId();
            }
            if(in_array(8, $sites)){
                $dom->setSite($em->getRepository(Site::class)->find(8));
            } else {
                $dom->setSite($em->getRepository(Site::class)->find(1));
            }

            $dom->setRmq($criteria['rmq']);
    }

    private function criteria($dom, $em)
    {
        $sousTypedocument = $form1Data['sousTypeDocument'];
            $catg = $form1Data['categorie'];
            $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
            
            if($CodeServiceofCours[0]['agence_ips'] === '50'){
                $rmq = $em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
           } else {
                $rmq = $em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
           }
          return  [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq,
            'categorie' => $catg
            ];
    }


    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     * @param [type] $form
     * @param [type] $dits
     * @param [type] $nomFichier
     * @return void
     */
    private function uplodeFile($form, $dom, $nomFichier, &$pdfFiles)
    {
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = $dom->getNumeroOrdreMission(). '_0'. substr($nomFichier,-1,1) . '.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_ENV['BASE_PATH_FICHIER'].'/dom/fichier/';
     
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }

        $setPieceJoint = 'set'.ucfirst($nomFichier);
        $dom->$setPieceJoint($fileName);
    }

    private function envoiePieceJoint($form, $dom, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i=1; $i < 3; $i++) { 
            $nom = "pieceJoint{$i}";
            if($form->get($nom)->getData() !== null){
                $this->uplodeFile($form, $dom, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_ENV['BASE_PATH_FICHIER'].'/dom/' . $dom->getNumeroOrdreMission(). '_' .  $dom->getAgenceEmetteurId()->getCodeAgence() . $dom->getServiceEmetteurId()->getCodeService(). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_ENV['BASE_PATH_FICHIER'].'/dom/' . $dom->getNumeroOrdreMission(). '_' . $dom->getAgenceEmetteurId()->getCodeAgence() . $dom->getServiceEmetteurId()->getCodeService(). '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    private function enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, $em)
    {
        $statutDemande = $em->getRepository(StatutDemande::class)->find(1);
        if($domForm->getModePayement() === 'MOBILE MONEY'){
            $mode = $form->get('mode')->getData();
            if (substr($form->get('mode')->getData(),0,4) === '+261') {
                $numTel = str_replace('+261', '0', $form->get('mode')->getData());
                $mode = str_replace('+261', '0',$form->get('mode')->getData());
            } else {
                $numTel = $form->get('mode')->getData();
                $mode = $form->get('mode')->getData();
            }
            
        } else if($domForm->getModePayement() === 'VIREMENT BANCAIRE') {
            $mode = $form->get('mode')->getData();
            $numTel ='';
        } else {
            $mode = '';
            $numTel = '';
        }
        $agenceDebiteur = $domForm->getAgence();
        $serviceDebiteur= $domForm->getService();
        $agenceEmetteur= $em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($domForm->getAgenceEmetteur(),0,2)]);
        $serviceEmetteur= $em->getRepository(Service::class)->findOneBy(['codeService' => substr($domForm->getServiceEmetteur(),0,3)]);
        $supplementJournaliere = $form->get('supplementJournaliere')->getData();
    
        if ($form1Data['salarier'] === "TEMPORAIRE") {
            $dom->setNom($form1Data['nom']);
            $dom->setPrenom($form1Data['prenom']);
            $dom->setCin($form1Data['cin']);
        } else {
            $personnel = $em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
            $dom->setNom($personnel->getNom());
            $dom->setPrenom($personnel->getPrenoms());
        }

        $sousTypeDocument = $em->getRepository(SousTypeDocument::class)->find($form1Data['sousTypeDocument']->getId());
        if (isset($form1Data['categorie'])) {
            $categoryId = $em->getRepository(Catg::class)->find($form1Data['categorie']->getId());
        } else {
            $categoryId = null;
        }

        if($form1Data['salarier'] === 'TEMPORAIRE'){
            $cin = $form1Data["cin"];
             $matricule = "XER00 -" . $cin . " - TEMPORAIRE";
        } else {
                $matricule = $form1Data['matricule'];
        }
        
        if ($form1Data['sousTypeDocument']->getCodeSousType() === 'FRAIS EXCEPTIONNEL') {
            $site = $em->getRepository(Site::class)->find(1);
        } else {
            $site = $domForm->getSite();
        }

        $dom
            ->setTypeDocument($form1Data['sousTypeDocument']->getCodeDocument())
            ->setSousTypeDocument($sousTypeDocument)
            ->setCategorie($categoryId)
            ->setMatricule($matricule)
            ->setUtilisateurCreation($_SESSION['user'])
            ->setNomSessionUtilisateur($_SESSION['user'])
            ->setNumeroOrdreMission($this->autoINcriment('DOM'))
            ->setIdStatutDemande($statutDemande)
            ->setCodeAgenceServiceDebiteur($agenceDebiteur->getCodeagence().$serviceDebiteur->getCodeService())
            ->setModePayement($domForm->getModePayement().':'.$mode)
            ->setCodeStatut($statutDemande->getCodeStatut())
            ->setNumeroTel($numTel)
            ->setLibelleCodeAgenceService($agenceEmetteur->getLibelleAgence().'-'.$serviceEmetteur->getLibelleService())
            ->setDroitIndemnite($supplementJournaliere)
            ->setAgenceEmetteurId($agenceEmetteur)
            ->setServiceEmetteurId($serviceEmetteur)
            ->setAgenceDebiteurId($agenceDebiteur)
            ->setServiceDebiteurId($serviceDebiteur)
            ->setCategoryId($categoryId)
            ->setSiteId($site)
            ->setHeureDebut($domForm->getHeureDebut()->format('H:i'))
            ->setHeureFin($domForm->getHeureFin()->format('H:i'))
            ->setEmetteur($domForm->getAgenceEmetteur().'-'.$domForm->getServiceEmetteur())
            ->setDebiteur($domForm->getAgence()->getLibelleAgence().'-'.$domForm->getService()->getLibelleService())
        ;
    }

    private function donnerPourPdf($dom, $domForm, $em)
    {
        if(explode(':',$dom->getModePayement())[0] === 'MOBILE MONEY' || explode(':',$dom->getModePayement())[0] === 'ESPECE'){
            $mode = 'TEL'.explode(':',$dom->getModePayement())[1];
        } else if(explode(':',$dom->getModePayement())[0] === 'VIREMENT BANCAIRE'){
            $mode = 'CPT'.explode(':',$dom->getModePayement())[1];
        } else {
            $mode = 'TEL'.explode(':',$dom->getModePayement())[1];
        }

        $email = $em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $_SESSION['user']])->getMail();
        return  [
            "Devis" => $dom->getDevis(),
            "Prenoms" => $dom->getPrenom(),
            "AllMontant" => $dom->getTotalGeneralPayer(),
            "Code_serv" => $dom->getAgenceEmetteur(),
            "dateS" => $dom->getDateDemande()->format("d/m/Y"),
            "NumDom" => $dom->getNumeroOrdreMission(),
            "serv" => $dom->getServiceEmetteur(),
            "matr" => $dom->getMatricule(),
            "typMiss" => $dom->getSousTypeDocument()->getCodeSousType(),

            "Nom" => $dom->getNom(),
            "NbJ" => $dom->getNombreJour(),
            "dateD" => $dom->getDateDebut()->format("d/m/Y"),
            "heureD" => $dom->getHeureDebut(),
            "dateF" => $dom->getDateFin(),
            "heureF" => $dom->getHeureFin(),
            "motif" => $dom->getMotifDeplacement(),
            "Client" => $dom->getClient(),
            "fiche" => $dom->getFiche(),
            "lieu" => $dom->getLieuIntervention(),
            "vehicule" => $dom->getVehiculeSociete(),
            "numvehicul" => $dom->getNumVehicule(),
            "idemn" => $dom->getIndemniteForfaitaire(),
            "totalIdemn" => $dom->getTotalIndemniteForfaitaire(),
            "motifdep01" => $dom->getMotifAutresDepense1(),
            "montdep01" => $dom->getAutresDepense1(),
            "motifdep02" => $dom->getMotifAutresDepense2(),
            "montdep02" => $dom->getAutresDepense2(),
            "motifdep03" => $dom->getMotifAutresDepense3(),
            "montdep03" => $dom->getAutresDepense3(),
            "totaldep" => $dom->getTotalAutresDepenses(),
            "libmodepaie" => explode(':',$dom->getModePayement())[0],
            "mode" => $mode,
            "codeAg_serv" => substr($domForm->getAgenceEmetteur(),0,2).substr($domForm->getServiceEmetteur(),0,3),
            "CategoriePers" => $dom->getCategorie() === null ? '' : $dom->getCategorie()->getDescription(),
            "Site" => $dom->getSite() === null ? '' : $dom->getSite()->getNomZone(),
            "Idemn_depl" => $dom->getIdemnityDepl(),
            "MailUser" => $email,
            "Bonus" => $dom->getDroitIndemnite(),
            "codeServiceDebitteur" => $dom->getAgence()->getCodeAgence(),
            "serviceDebitteur" => $dom->getService()->getCodeService()
        ];
    }

    private function enregistreDernierNumDansApplication($dom, $em)
    {
        $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => 'DOM']);
        $application->setDerniereId($dom->getNumeroOrdreMission());
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $em->persist($application);
        $em->flush();
    }

    public function recupAppEnvoiDbEtPdf($dom, $domForm, $form, $em)
    {
        
            //RECUPERATION de la dernière NumeroDordre de mission 
            $this->enregistreDernierNumDansApplication($dom, $em);

            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            $em->persist($dom);
            $em->flush();

            $tabInternePdf = $this->donnerPourPdf($dom, $domForm, $em);
            $genererPdfDom = new GeneratePdfDom();
            $genererPdfDom->genererPDF($tabInternePdf);

            $this->envoiePieceJoint($form, $dom, $this->fusionPdf);
    }

    private function verifierSiDateExistant(string $matricule,  $dateDebutInput, $dateFinInput): bool
    {
        
            $Dates = $this->DomModel->getInfoDOMMatrSelet($matricule);
       
        $trouve = false; // Variable pour indiquer si la date est trouvée

        // Parcourir chaque élément du tableau
        foreach ($Dates as $periode) {
            // Convertir les dates en objets DateTime pour faciliter la comparaison
            $dateDebut = new DateTime($periode['Date_Debut']);
            $dateFin = new DateTime($periode['Date_Fin']);
            $dateDebutInputObj = $dateDebutInput; // Correction de la variable
            $dateFinInputObj = $dateFinInput; // Correction de la variable

            // Vérifier si la date à vérifier est comprise entre la date de début et la date de fin
            if (($dateFinInputObj >= $dateDebut && $dateFinInputObj <= $dateFin) || ($dateDebutInputObj >= $dateDebut && $dateDebutInputObj <= $dateFin) || ($dateDebutInputObj === $dateFin)) { // Correction des noms de variables
                $trouve = true;
                return $trouve;
            }
        }

        // Vérifier si aucune correspondance n'est trouvée
        return $trouve;
    }
}