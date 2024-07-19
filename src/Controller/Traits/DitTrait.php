<?php

namespace App\Controller\Traits;

use App\Entity\User;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\StatutDemande;
use App\Entity\WorNiveauUrgence;
use App\Entity\DemandeIntervention;



trait DitTrait
{
    

    private function insertDemandeIntervention($dits, DemandeIntervention $demandeIntervention) : DemandeIntervention
    {
            $demandeIntervention->setObjetDemande($dits->getObjetDemande());
            $demandeIntervention->setDetailDemande($dits->getDetailDemande());
            $demandeIntervention->setTypeDocument($dits->getTypeDocument());
            $demandeIntervention->setCategorieDemande($dits->getCategorieDemande());
            $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
            $demandeIntervention->setDemandeDevis($dits->getDemandeDevis());
            $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
            //AGENCE - SERVICE
            $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).'-'.substr($dits->getServiceEmetteur(), 0, 3));
            $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().'-'. $dits->getService()->getCodeService());
            //INTERVENTION
            $demandeIntervention->setIdNiveauUrgence($dits->getIdNiveauUrgence());
            $demandeIntervention->setDatePrevueTravaux($dits->getDatePrevueTravaux());
            //REPARATION
            $demandeIntervention->setTypeReparation($dits->getTypeReparation());
            $demandeIntervention->setReparationRealise($dits->getReparationRealise());
            $demandeIntervention->setInternetExterne($dits->getInternetExterne());
            //INFO CLIENT
            $demandeIntervention->setNomClient($dits->getNomClient());
            $demandeIntervention->setNumeroTel($dits->getNumeroTel());
            $demandeIntervention->setClientSousContrat($dits->getClientSousContrat());
            //INFORMATION MATERIEL
            $data = $this->ditModel->findAll($dits->getIdMateriel(), $dits->getNumParc(), $dits->getNumSerie());
            $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
            //PIECE JOINT
            $demandeIntervention->setPieceJoint01($dits->getPieceJoint01());
            $demandeIntervention->setPieceJoint02($dits->getPieceJoint02());
            $demandeIntervention->setPieceJoint03($dits->getPieceJoint03());

            //INFORMATION ENTRER MANUELEMENT
            $demandeIntervention->setIdStatutDemande($dits->getIdStatutDemande());
            $demandeIntervention->setNumeroDemandeIntervention($dits->getNumeroDemandeIntervention());
            $demandeIntervention->setMailDemandeur($dits->getMailDemandeur());
            $demandeIntervention->setDateDemande($dits->getDateDemande());
            $demandeIntervention->setHeureDemande($dits->getHeureDemande());
            $demandeIntervention->getUtilisateurDemandeur($dits->getUtilisateurDemandeur());

            
        return $demandeIntervention;
    }

    private function pdfDemandeIntervention($dits, DemandeIntervention $demandeIntervention) : DemandeIntervention
    {
            
        
        //Objet - Detail
        $demandeIntervention->setObjetDemande($dits->getObjetDemande());
        $demandeIntervention->setDetailDemande($dits->getDetailDemande());
        //Categorie - avis recouvrement - devis demandé
        $demandeIntervention->setCategorieDemande($dits->getCategorieDemande());
        $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
        $demandeIntervention->setDemandeDevis($dits->getDemandeDevis());

        //Intervention
        $demandeIntervention->setIdNiveauUrgence($dits->getIdNiveauUrgence());
        $demandeIntervention->setDatePrevueTravaux($dits->getDatePrevueTravaux());

        //Agence - service
        $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).'-'.substr($dits->getServiceEmetteur(), 0, 3));
        $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().'-'. $dits->getService()->getCodeService());

        //REPARATION
        $demandeIntervention->setTypeReparation($dits->getTypeReparation());
        $demandeIntervention->setReparationRealise($dits->getReparationRealise());
        if($dits->getInternetExterne() === 'I'){
            $dits->setInternetExterne('INTERNE');
        } elseif($dits->getInternetExterne() === 'E') {
            $dits->setInternetExterne('EXTERNE');
        }
        $demandeIntervention->setInternetExterne($dits->getInternetExterne());
        
        //INFO CLIENT
        $demandeIntervention->setNomClient($dits->getNomClient());
        $demandeIntervention->setNumeroTel($dits->getNumeroTel());
        $demandeIntervention->setClientSousContrat($dits->getClientSousContrat());
        
        if(!empty($dits->getIdMateriel()) || !empty($dits->getNumParc()) || !empty($dits->getNumSerie())){

            //Caractéristiques du matériel
            $data = $this->ditModel->findAll($dits->getIdMateriel(), $dits->getNumParc(), $dits->getNumSerie());
            $demandeIntervention->setNumParc($data[0]['num_parc']);
            $demandeIntervention->setNumSerie($data[0]['num_serie']);
            $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
            $demandeIntervention->setConstructeur($data[0]['constructeur']);
            $demandeIntervention->setModele($data[0]['modele']);
            $demandeIntervention->setDesignation($data[0]['designation']);
            $demandeIntervention->setCasier($data[0]['casier_emetteur']);
            $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
            //Bilan financière
            $demandeIntervention->setCoutAcquisition($data[0]['prix_achat']);
            $demandeIntervention->setAmortissement($data[0]['amortissement']);
            $demandeIntervention->setChiffreAffaire($data[0]['chiffreaffaires']);
            $demandeIntervention->setChargeEntretient($data[0]['chargeentretien']);
            $demandeIntervention->setChargeLocative($data[0]['chargelocative']);
            //Etat machine
            $demandeIntervention->setKm($data[0]['km']);
            $demandeIntervention->setHeure($data[0]['heure']);
        }
        
        //INFORMATION ENTRER MANUELEMENT
        $demandeIntervention->setNumeroDemandeIntervention($dits->getNumeroDemandeIntervention());
        $demandeIntervention->setMailDemandeur($dits->getMailDemandeur());
        $demandeIntervention->setDateDemande($dits->getDateDemande());
        


        return $demandeIntervention;
    }

    private function historiqueInterventionMateriel($dits): array
    {
        $historiqueMateriel = $this->ditModel->historiqueMateriel($dits->getIdMateriel());
            foreach ($historiqueMateriel as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($key == "datedebut") {
                        $historiqueMateriel[$keys]['datedebut'] = implode('/', array_reverse(explode("-", $value)));
                    } elseif ($key === 'somme') {
                        $historiqueMateriel[$keys][$key] = explode(',', $this->formatNumber($value))[0];
                    }
                }
            }
        return $historiqueMateriel;
    }

    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     * @param [type] $form
     * @param [type] $dits
     * @param [type] $nomFichier
     * @return void
     */
    private function uplodeFile($form, $dits, $nomFichier, &$pdfFiles)
    {
        
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = $dits->getNumeroDemandeIntervention(). '_0'. substr($nomFichier,-1,1) . '.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/dit/fichier/';
        //$fileDossier = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\PRODUCTION\\DIT\\';
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }

        $setPieceJoint = 'set'.ucfirst($nomFichier);
        $dits->$setPieceJoint($fileName);

        
    }

    private function envoiePieceJoint($form, $dits, $fusionPdf)
{

    $pdfFiles = [];

    for ($i=1; $i < 4; $i++) { 
       $nom = "pieceJoint0{$i}";
       if($form->get($nom)->getData() !== null){
            $this->uplodeFile($form, $dits, $nom, $pdfFiles);
        }
    }
    array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/dit/' . $dits->getNumeroDemandeIntervention(). '_' . str_replace("-", "", $dits->getAgenceServiceEmetteur()). '.pdf');

    // Nom du fichier PDF fusionné
     $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/dit/' . $dits->getNumeroDemandeIntervention(). '_' . str_replace("-", "", $dits->getAgenceServiceEmetteur()). '.pdf';

     // Appeler la fonction pour fusionner les fichiers PDF
     if (!empty($pdfFiles)) {
         $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
     }
}



    /**
     * INFO AJOUTER MANUELEMENT DANS LA CLASSE DEMANDE D'INTERVENTION
     *
     * @param [type] $form
     * @param [type] $em
     * @return DemandeIntervention
     */
    private function infoEntrerManuel($form, $em) : DemandeIntervention
    {
        $dits = $form->getData();
        
        $dits->setUtilisateurDemandeur($_SESSION['user']);
            $dits->setHeureDemande($this->getTime());
            $dits->setDateDemande(new \DateTime($this->getDatesystem()));
            $statutDemande = $em->getRepository(StatutDemande::class)->find(50);
            $dits->setIdStatutDemande($statutDemande);
            $dits->setNumeroDemandeIntervention($this->autoDecrementDIT('DIT'));
            $email = $em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $_SESSION['user']])->getMail();
            $dits->setMailDemandeur($email);

         
            return $dits;
    }

/**
 * INITIALISER LA VALEUR DE LA FORMULAIRE
 *
 * @param DemandeIntervention $demandeIntervention
 * @param [type] $em
 * @return void
 */
private function initialisationForm(DemandeIntervention $demandeIntervention, $em)
{
    $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
    $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
    
    $demandeIntervention->setAgenceEmetteur($CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']) );
    $demandeIntervention->setServiceEmetteur($CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']));
    $demandeIntervention->setIdNiveauUrgence($em->getRepository(WorNiveauUrgence::class)->find(1));
    $idAgence = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $CodeServiceofCours[0]['agence_ips'] ])->getId();
    $demandeIntervention->setAgence($em->getRepository(Agence::class)->find($idAgence));
    $demandeIntervention->setService($em->getRepository(Service::class)->findOneBy(['codeService' => $CodeServiceofCours[0]['service_ips'] ]));
}


}