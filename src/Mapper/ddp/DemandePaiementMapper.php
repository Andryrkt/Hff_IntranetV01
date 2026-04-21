<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Constants\ddp\StatutConstants;
use App\Entity\ddp\HistoriqueStatutDdp;

class DemandePaiementMapper
{
    public static function map(DemandePaiementDto $dto): DemandePaiement
    {
        $basePathFichierCourt = $_ENV['BASE_PATH_FICHIER_COURT'];
        $numeroDdp = $dto->numeroDdp;
        $nomFichier = $numeroDdp . '.pdf';
        $nomFichierAvecCheminDistant = "\\\\192.168.0.28\c$\wamp64\www{$basePathFichierCourt}ddp\\{$numeroDdp}\\{$nomFichier}";
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
            ->setFicherDdpa($nomFichierAvecCheminDistant)
            ->setNumeroSoumissionDdpDa($dto->numeroSoumissionDdpDa ?? null)
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
        ;

        return $ddp;
    }

    public static function mapInverse(array $ddps): array
    {
        $dtos = [];
        foreach ($ddps as $ddp) {
            $dto = new DemandePaiementDto();
            $dto->numeroDdp = $ddp->getNumeroDdp();
            $dto->numeroCla = $ddp->getNumeroCla();
            $dto->numeroDemandeAppro = $ddp->getNumeroDemandeAppro();
            $dto->typeDemande = $ddp->getTypeDemandeId();
            $dto->numeroFournisseur = $ddp->getNumeroFournisseur();
            $dto->beneficiaire = $ddp->getBeneficiaire();
            $dto->numeroCommande =  $ddp->getNumeroCommande();
            $numsLivraisons = [];
            foreach ($ddp->getCommandeLivraisons() as $livraison) {
                if ($livraison->getNumeroLivraison()) {
                    $numsLivraisons[] = $livraison->getNumeroLivraison();
                }
            }
            $dto->numeroLivraison = empty($numsLivraisons) ? null : implode(';', $numsLivraisons);
            $dto->numeroFacture =  $ddp->getNumeroFacture();
            $dto->statut = $ddp->getStatut();
            $dto->montantAPayer = $ddp->getMontantAPayers();
            $dto->dateSoumissionCompta = $ddp->getDateSoumissionCompta();
            $dtos[] = $dto;
        }

        return $dtos;
    }

    public static function mapBap(DemandePaiementDto $dto): DemandePaiement
    {
        $ddp = new DemandePaiement();
        $ddp
            ->setNumeroDdp($dto->numeroDdp)
            ->setTypeDemandeId($dto->typeDemande)
            ->setNumeroFournisseur($dto->numeroFournisseur)
            ->setRibFournisseur($dto->ribFournisseur)
            ->setBeneficiaire($dto->beneficiaire)
            ->setMotif("Bon a payer {$dto->numeroFournisseur} - {$dto->numeroFacture}")
            ->setAgenceDebiter($dto->debiteur['agence']->getCodeAgence())
            ->setServiceDebiter($dto->debiteur['service']->getCodeService())
            ->setStatut(StatutConstants::BAP_A_TRANSMETTRE)
            ->setAdresseMailDemandeur($dto->adresseMailDemandeur)
            ->setDemandeur($dto->demandeur)
            ->setModePaiement($dto->modePaiement)
            ->setMontantAPayers($dto->montantAPayer)
            ->setContact(Null)
            ->setNumeroCommande($dto->numeroCommande)
            ->setNumeroFacture($dto->numeroFacture)
            ->setStatutDossierRegul(Null)
            ->setNumeroVersion(1)
            ->setDevise($dto->devise)
            ->setEstAutreDoc(false)
            ->setNomAutreDoc(Null)
            ->setEstCdeClientExterneDoc(false)
            ->setNomCdeClientExterneDoc(Null)
            ->setNumeroDossierDouane(Null)
            ->setAppro(true)
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
            ->setNumeroSoumissionDdpDa($dto->numeroSoumissionDdpDa ?? null)
        ;
        return $ddp;
    }


    public static function mapUpdate(DemandePaiementDto $dto, DemandePaiement $ddp): DemandePaiement
    {
        return $ddp->setStatut(StatutConstants::SOUMIS_A_VALIDATION)
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
