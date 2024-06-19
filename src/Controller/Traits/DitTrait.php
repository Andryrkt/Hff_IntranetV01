<?php

namespace App\Controller\Traits;

use App\Entity\DemandeIntervention;



trait DitTrait
{
    

    private function insertDemandeIntervention($dits, DemandeIntervention $demandeIntervention) : DemandeIntervention
    {
            $demandeIntervention->setObjetDemande($dits->getObjetDemande());
            $demandeIntervention->setDetailDemande($dits->getDetailDemande());
            $demandeIntervention->setTypeDocument($dits->getTypeDocument());
            $demandeIntervention->setCodeSociete($dits->getCodeSociete());
            $demandeIntervention->setCategorieDemande($dits->getCategorieDemande());
            $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
            $demandeIntervention->setDemandeDevis($dits->getDemandeDevis());
            $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
            //AGENCE - SERVICE
            $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).''.substr($dits->getServiceEmetteur(), 0, 3));
            $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().''. $dits->getService()->getCodeService());
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
            $demandeIntervention->setIdMateriel($dits->getIdMateriel());
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

        //Intervention
        $demandeIntervention->setIdNiveauUrgence($dits->getIdNiveauUrgence());
        $demandeIntervention->setDatePrevueTravaux($dits->getDatePrevueTravaux());

        //Agence - service
        $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).'-'.substr($dits->getServiceEmetteur(), 0, 3));
        $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().'-'. $dits->getService()->getCodeService());

        //REPARATION
        $demandeIntervention->setTypeReparation($dits->getTypeReparation());
        $demandeIntervention->setReparationRealise($dits->getReparationRealise());
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
}