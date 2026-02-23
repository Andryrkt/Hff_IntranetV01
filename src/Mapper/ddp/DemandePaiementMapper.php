<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Constants\ddp\StatutConstants;
use App\Entity\ddp\HistoriqueStatutDdp;

class DemandePaiementMapper
{
    public static function map($dto, string $nomAvecCheminFichier): DemandePaiement
    {
        $basePathFichierCourt = $_ENV['BASE_PATH_FICHIER_COURT'];
        $numeroDdp = $dto->numeroDdp;
        $nomFichierAvecCheminDistant = "\\\\192.168.0.28\c$\wamp64\www{$basePathFichierCourt}ddp\\{$numeroDdp}_New_1\\{$nomFichier}";
        $ddp = new DemandePaiement();
        $ddp->setNumeroDdp($dto->numeroDdp)
            ->setTypeDemandeId($dto->typeDemande)
            ->setNumeroFournisseur($dto->numeroFournisseur)
            ->setRibFournisseur($dto->ribFournisseur)
            ->setBeneficiaire($dto->beneficiaire)
            ->setMotif($dto->motif)
            ->setAgenceDebiter($dto->debiteur['agence']->getCodeAgence())
            ->setServiceDebiter($dto->debiteur['service']->getCodeService())
            ->setStatut($dto->statut)
            ->setAdresseMailDemandeur($dto->adresseMailDemandeur)
            ->setDemandeur($dto->demandeur)
            ->setModePaiement($dto->modePaiement)
            ->setMontantAPayers($dto->montantAPayer())
            ->setContact($dto->contact)
            ->setNumeroCommande($dto->numeroCommande)
            ->setNumeroFacture($dto->numeroFacture)
            ->setDevise($dto->devise)
            ->setStatutDossierRegul($dto->statutDossierRegul)
            ->setNumeroVersion($dto->numeroVersion)
            ->setEstAutreDoc($dto->estAutresDoc)
            ->setNomAutreDoc($dto->nomAutreDoc)
            ->setEstCdeClientExterneDoc($dto->estCdeClientExterneDoc)
            ->setNomCdeClientExterneDoc($dto->nomCdeClientExterneDoc)
            ->setNumeroDossierDouane($dto->numeroDossierDouane)
            ->setAppro($dto->appro)
            ->setTypeDa($dto->typeDa)
            ->setNumeroVersionBc($dto->numeroVersionBc)
            ->setFicherDdpa($nomFichierAvecCheminDistant);

        return $ddp;
    }

    public static function mapUpdate(DemandePaiementDto $dto, DemandePaiement $ddp): DemandePaiement
    {
        return $ddp->setStatut(StatutConstants::STATUT_SOUMIS_A_VALIDATION)
            ->setMontantApayer($dto->montantAPayer)
            ->setRibFournisseur($dto->ribFournisseur)
            ->setEstAutreDoc($dto->estAutresDoc)
            ->setNomAutreDoc($dto->nomAutreDoc)
            ->setEstCdeClientExterneDoc($dto->estCdeClientExterneDoc)
            ->setNomCdeClientExterneDoc($dto->nomCdeClientExterneDoc)
            ->setAppro($dto->appro)
            ->setDevise($dto->devise)
            ->setModePaiement($dto->modePaiement)
        ;
    }

    public static function mapStatut(DemandePaiementDto $dto): HistoriqueStatutDdp
    {
        $historiqueStatutDdp = new HistoriqueStatutDdp();
        return $historiqueStatutDdp
            ->setNumeroDdp($dto->numeroDdp)
            ->setStatut($dto->statut)
            ->setDate(new \DateTime())
        ;
    }
}
