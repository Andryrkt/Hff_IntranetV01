<?php

namespace App\Service\dit\transfer;

use App\Entity\dit\BcSoumis;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitDevisSoumisAValidation;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\Console\Helper\ProgressBar;

class TransformerEnObjetService
{

    private DemandeIntervention $dit;
    private DitOrsSoumisAValidation $ditOrsSoumis;
    private DitDevisSoumisAValidation $ditDevisSoumis;
    private BcSoumis $bcSoumis;

    public function __construct()
    {
        $this->dit = new DemandeIntervention();
        $this->ditOrsSoumis = new DitOrsSoumisAValidation();
        $this->ditDevisSoumis = new DitDevisSoumisAValidation();
        $this->bcSoumis = new BcSoumis();
    }

    /**
     * cette Methode permet de transformer un tableau en Objet
     *
     * @param array $ancienDits
     * @return array tableau d'ojet demande d'intervention
     */
    public function transformDitEnObjet(array $ancienDits, ProgressBar $progressBar): array
    {  
        $ditAnciens = [];
        foreach ($ancienDits as  $ancienDit) {
            $ditAnciens[] = $this->ditEnObjet($ancienDit);
            
            // Faire avancer la barre de progression
            $progressBar->advance();
        }

        return $ditAnciens;
    }

    private function ditEnObjet(array $dits): DemandeIntervention
    {
        return $this->dit
            ->setNumeroDemandeIntervention($dits['NumeroDemandeIntervention'])
            ->setTypeDocument($dits['TypeDocument'])
            
            ->setTypeReparation($dits['TypeReparation'])
            ->setReparationRealise($dits['ReparationRealise'])
            
            ->setCategorieDemande($dits['CategorieDemande'])
            ->setInternetExterne($dits['InternetExterne'])

            //AGENCE - SERVICE
            ->setAgenceServiceEmetteur($dits['AgenceServiceEmetteur'])
            ->setAgenceServiceDebiteur($dits['AgenceServiceDebiteur'])
            //Agence et service emetteur debiteur ID
            ->setAgenceEmetteurId($dits['AgenceEmetteurId'])
            ->setServiceEmetteurId($dits['ServiceEmetteurId'])
            ->setAgenceDebiteurId($dits['AgenceDebiteurId'])
            ->setServiceDebiteurId($dits['ServiceDebiteurId'])

            //INFO CLIENT
            ->setNomClient($dits['NomClient'])
            ->setNumeroTel($dits['NumeroTel'])
            ->setClientSousContrat($dits['ClientSousContrat'])
            ->setMailClient($dits['MailClient'])
            ->setNumeroClient($dits['NumeroClient'])


            //INFO DEMANDE
            ->setDatePrevueTravaux($dits['DatePrevueTravaux'])
            ->setDemandeDevis($dits['DemandeDevis'])
            ->setIdNiveauUrgence($dits['IdNiveauUrgence'])
            ->setObjetDemande($dits['ObjetDemande'])
            ->setDetailDemande($dits['DetailDemande'])
            ->setLivraisonPartiel($dits['LivraisonPartiel'])

            ->setIdStatutDemande($dits['IdStatutDemande'])
            ->setAvisRecouvrement($dits['AvisRecouvrement'])
            ->setDateDemande($dits['DateDemande'])
            ->setHeureDemande($dits['HeureDemande'])

            //INFO DEMANDEUR
            ->setMailDemandeur($dits['MailDemandeur'])
            ->setUtilisateurDemandeur($dits['UtilisateurDemandeur'])

            //INFORMATION MATERIEL
            ->setIdMateriel($dits['IdMateriel'])
            ->setKm($dits['Km'])
            ->setHeure($dits['Heure'])

            //PIECE JOINT
            ->setPieceJoint01($dits['PieceJoint01'])
            ->setPieceJoint02($dits['PieceJoint02'])
            ->setPieceJoint03($dits['PieceJoint03'])

            //INFO OR
            ->setNumeroOR($dits['NumeroOR'])
            ->setStatutOr($dits['StatutOr'])
            ->setDateValidationOr($dits['DateValidationOr'])

            //INFO DEVIS
            ->setNumeroDevisRattache($dits['NumeroDevisRattache'])
            ->setStatutDevis($dits['StatutDevis'])

            //MIGRATION
            ->setMigration(1)
        ;

    }

    public function devisEnObjet(array $dev): DitDevisSoumisAValidation
    {
        return $this->ditDevisSoumis
            ->setNumeroDit($dev['NumeroDit'])
            ->setNumeroDevis($dev['NumeroDevis'])
            ->setNumeroItv($dev['NumeroItv'])
            ->setNombreLigneItv($dev['NombreLigneItv'])
            ->setMontantItv($dev['MontantItv'])
            ->setNumeroVersion($dev['NumeroVersion'])
            ->setMontantPiece($dev['MontantPiece'])
            ->setMontantMo($dev['MontantMo'])
            ->setMontantAchatLocaux($dev['MontantAchatLocaux'])
            ->setMontantFraisDivers($dev['MontantFraisDivers'])
            ->setMontantLubrifiants($dev['MontantLubrifiants'])
            ->setLibellelItv($dev['LibellelItv'])
            ->setStatut($dev['Statut'])
            ->setDateHeureSoumission($dev['DateHeureSoumission'])
            ->setMontantForfait($dev['MontantForfait'])
            ->setNatureOperation($dev['NatureOperation'])
            ->setDevise($dev['Devise'])
            ->setDevisVenteOuForfait($dev['DevisVenteOuForfait'])
        ;
    }

    public function dataAinsereDansTableBcSoumis(array $bcs): BcSoumis
    {
        return $this->bcSoumis
            ->setNumDit('')
            ->setNumDevis('')
            ->setNumBc('')
            ->setNumVersion('')
            ->setDateBc('')
            ->setDateDevis('')
            ->setMontantDevis('')
            ->setDateHeureSoumission('')
            ->setNomFichier('')
        ;
    }

    public function dataAinsereDansTableOrSoumis(array $ors): DitOrsSoumisAValidation
    {
        return $this->ditOrsSoumis
            ->setNumeroOR($ors['NumeroOR'])
            ->setNumeroItv($ors[''])
            ->setNombreLigneItv($ors[''])
            ->setMontantItv($ors[''])
            ->setNumeroVersion($ors[''])
            ->setMontantPiece($ors[''])
            ->setMontantMo($ors[''])
            ->setMontantAchatLocaux($ors[''])
            ->setMontantFraisDivers($ors[''])
            ->setMontantLubrifiants($ors[''])
            ->setLibellelItv($ors[''])
            ->setDateSoumission($ors[''])
            ->setHeureSoumission($ors[''])
            ->setStatut($ors[''])
            ->setMigration($ors[''])
        ;
    }
}