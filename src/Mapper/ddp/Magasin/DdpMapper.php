<?php

namespace App\Mapper\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Entity\ddp\DemandePaiement;

class DdpMapper
{
    /**
     * @param DdpDto $dto
     * @return DemandePaiement
     */
    public static function map(DdpDto $dto): DemandePaiement
    {
        $basePathFichierCourt = $_ENV['BASE_PATH_FICHIER_COURT'];
        $numeroDdp = $dto->numeroDdp;
        $nomFichier = $numeroDdp . '.pdf';
        $nomFichierAvecCheminDistant = "\\\\192.168.0.28\c$\wamp64\www{$basePathFichierCourt}ddp\\{$numeroDdp}\\{$nomFichier}";
        $ddp = new DemandePaiement();
        $ddp->setNumeroDdp($dto->numeroDdp)
            ->setTypeDemandeId($dto->typeDdp)
            ->setNumeroFournisseur($dto->numeroFournisseur)
            ->setRibFournisseur($dto->ribFournisseur)
            ->setBeneficiaire($dto->beneficiaire)
            ->setMotif($dto->motif)
            ->setAgenceDebiter('01')
            ->setServiceDebiter('NEG')
            ->setStatut($dto->statut)
            ->setAdresseMailDemandeur($dto->adresseMailDemandeur)
            ->setDemandeur($dto->demandeur)
            ->setModePaiement($dto->modePaiement)
            ->setMontantAPayers($dto->montantAPayer())
            ->setContact($dto->contact)
            ->setNumeroCommande($dto->numeroCommandeSansGuillemet())
            ->setNumeroFacture($dto->numeroFactureSansGuillemet())
            ->setDevise($dto->devise)
            ->setStatutDossierRegul(null)
            ->setNumeroVersion($dto->numeroVersion)
            ->setEstAutreDoc($dto->estAutreDoc)
            ->setNomAutreDoc($dto->nomAutreDoc)
            ->setEstCdeClientExterneDoc($dto->estCdeClientExterneDoc)
            ->setNomCdeClientExterneDoc($dto->nomCdeClientExterneDoc)
            ->setNumeroDossierDouane($dto->numeroDossierDouane)
            ->setCodeSociete($dto->codeSociete)
        ;

        return $ddp;
    }
}
