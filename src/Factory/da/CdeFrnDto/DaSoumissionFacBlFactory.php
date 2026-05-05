<?php

namespace App\Factory\da\CdeFrnDto;

use App\Constants\ddp\StatutConstants;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Dto\Da\ListeCdeFrn\DaDdpaDto;
use App\Dto\Da\ListeCdeFrn\DaSituationReceptionDto;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\da\DaSoumissionBc;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Model\da\DaSoumissionFacBlModel;
use App\Service\autres\AutoIncDecService;
use App\Service\da\DaSoumissionCalculService;
use App\Service\da\DaSoumissionDataService;
use App\Service\da\NumeroGenerateurService;
use App\Service\security\SecurityService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionFacBlFactory
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private EntityManagerInterface $em;
    private DaSoumissionFacBlModel $daSoumissionFacBlModel;

    private NumeroGenerateurService $numeroGenerateurService;
    private DaSoumissionCalculService $calculService;
    private DaSoumissionDataService $dataService;
    private SecurityService $securityService;

    public function __construct(
        EntityManagerInterface $em,
        DaSoumissionFacBlModel $daSoumissionFacBlModel,
        NumeroGenerateurService $numeroGenerateurService,
        DaSoumissionCalculService $calculService,
        DaSoumissionDataService $dataService,
        SecurityService $securityService
    ) {
        $this->em = $em;
        $this->daSoumissionFacBlModel = $daSoumissionFacBlModel;
        $this->numeroGenerateurService = $numeroGenerateurService;
        $this->calculService = $calculService;
        $this->dataService = $dataService;
        $this->securityService = $securityService;
    }

    public function initialisation(
        string $numCde,
        string $numDa,
        string $numOr,
        string $codeSociete
    ): DaSoumissionFacBlDto {
        $dto = new DaSoumissionFacBlDto();

        $dto->numeroCde = $numCde;
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroOR = $numOr;
        $dto->codeSociete = $codeSociete;
        $dto->numeroDemandeDit = $this->dataService->getNumeroDit($dto->numeroDemandeAppro, $dto->codeSociete);
        $dto->utilisateur = $this->securityService->getUserName();
        $dto->numeroVersionFacBl = $this->dataService->getNumeroVersion($dto->numeroCde, $dto->codeSociete);
        $dto->dateBlFac = $this->dataService->getDateLivraisonPrevue($dto->numeroDemandeAppro, $dto->numeroCde, $dto->codeSociete);
        $dto->dateDemande = new DateTime();

        // livraison ===========================
        $dto->infoLiv = $this->dataService->getInfoLivraison($dto->numeroCde, $dto->numeroDemandeAppro, $dto->codeSociete);
        $dto->numLiv = array_keys($dto->infoLiv);

        // info commande (BC) ==========================
        $dto->infoBc = $this->dataService->getInfoBc($dto->numeroCde, $dto->codeSociete);
        $dto->numeroFournisseur = $dto->infoBc['num_fournisseur'];
        $dto->nomFournisseur = $dto->infoBc['nom_fournisseur'];

        // DDPL ==========================
        $dto->statutFacBl = self::STATUT_SOUMISSION;
        $dto->totalMontantCommande = $this->calculService->getTotalMontantCommande($dto->numeroCde);

        // BAP =======
        $dto->numeroBap = $this->genererNumeroBap();

        // recuperation des demandes de paiement déjà payer
        $this->getDdpa($dto);

        $this->calculService->calculerMontantEtRatios($dto);

        // recupération des informations de commande
        $this->getReception($dto);

        return $dto;
    }

    public function EnrichissementDtoApresSoumission(DaSoumissionFacBlDto $dto, ?string $nomPdfFusionner = null)
    {
        if (empty($nomPdfFusionner)) return $dto;

        $dto->pieceJoint1 = $nomPdfFusionner;
        $dto->montantBlFacture = (float)str_replace(',', '.', str_replace(' ', '', $dto->montantBlFacture ?? '0'));
        $dto->numeroFactureFournisseur = $this->getNumFacEtMontant($dto->numLiv, $dto->codeSociete)[0]['numero_facture'];

        // Bon à payer (BAP) ===============================
        $dto->statutBap = StatutConstants::BAP_A_TRANSMETTRE;
        $dto->dateStatutBap = new DateTime();
        $dto->montantReceptionIps = $this->getNumFacEtMontant($dto->numLiv, $dto->codeSociete)[0]['montant_reception_ips'];

        // livraison ===========================
        $dto->dateClotLiv = new DateTime($dto->infoLiv[$dto->numLiv]['date_clot']);
        $dto->refBlFac = $dto->infoLiv[$dto->numLiv]['ref_fac_bl'];

        // recupération information de demande de paiement
        $dto->demandePaiementDto = $this->getDemandePaiement($dto);

        return $dto;
    }

    public function getReception(DaSoumissionFacBlDto $dto)
    {
        $articleCdes = $this->daSoumissionFacBlModel->getArticleCde($dto->numeroCde);

        foreach ($articleCdes as $articleCde) {
            $situRecepDto = new DaSituationReceptionDto();
            $dto->receptions[] = DaSoumissionFacBlMapper::mapReception($situRecepDto, $articleCde);
        }
    }

    public function getDdpa(DaSoumissionFacBlDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($dto->numeroCde);

        foreach ($ddps as  $ddp) {
            // Crée un nouveau DTO pour chaque élément afin d'avoir des objets distincts
            $ddpaDto = new DaDdpaDto();

            // Copie les propriétés nécessaires du DTO initial qui sont communes à tous les éléments
            $ddpaDto->totalMontantCommande = $dto->totalMontantCommande;

            // Mappe l'entité vers le nouveau DTO
            DaSoumissionFacBlMapper::mapDdp($ddpaDto, $ddp);

            $dto->daDdpa[] = $ddpaDto;
        }

        return $this->calculService->calculerCumulRatios($dto);
    }



    private function getDemandePaiement(DaSoumissionFacBlDto $dto): DemandePaiementDto
    {
        $ddpDto = new DemandePaiementDto();

        $daSoumissionBcRepository = $this->em->getRepository(DaSoumissionBc::class);
        $demandePaiementRepository = $this->em->getRepository(DemandePaiement::class);
        $infoDa = $this->dataService->getInfoDa($dto->numeroCde, $dto->codeSociete);

        $numeroSoumissionDdpDa = AutoIncDecService::autoIncrement(
            $demandePaiementRepository->getDernierNumeroSoumissionDdpDa($dto->numeroCde, $infoDa['numeroDemandeAppro'], $dto->codeSociete)
        );

        $infoFournisseur = $this->dataService->getInfoFournisseur($infoDa['numeroFournisseur'], $dto->numeroCde, $dto->codeSociete);

        if (!empty($infoFournisseur)) {
            $ddpDto->numeroFournisseur = $infoFournisseur[0]['num_fournisseur'];
            $ddpDto->ribFournisseur = $infoFournisseur[0]['rib_fournisseur'];
            $ddpDto->ribFournisseurAncien = $infoFournisseur[0]['rib_fournisseur'];
            $ddpDto->beneficiaire = $infoFournisseur[0]['nom_fournisseur'];
            $ddpDto->modePaiement = 'CHEQUE';
            $ddpDto->devise = $infoFournisseur[0]['devise'];
        }

        $ddpDto->numeroDdp = $dto->typeDdp !== 'bap' ? $this->genererNumeroDdp() : $dto->numeroBap;
        $ddpDto->debiteur = $this->dataService->resolveDebiteur($infoDa['daTypeId'], $infoDa);
        $ddpDto->typeDemande = $this->getTypeDdp($dto);
        $ddpDto->statut = $dto->montantAregulariser <= 0.0 ? StatutConstants::DDPR_A_TRANSMETTRE : StatutConstants::DDPL_A_TRANSMETTRE;
        $ddpDto->demandeur = $this->securityService->getUserName();
        $ddpDto->adresseMailDemandeur = $this->securityService->getUserEmail();
        $ddpDto->montantAPayer = $dto->montantAregulariser;
        $ddpDto->numeroCommande = $dto->numeroCde;
        $ddpDto->numeroFacture = $dto->numeroFactureFournisseur;
        $ddpDto->appro = true;
        $ddpDto->typeDa = $infoDa['daTypeId'];
        $ddpDto->numeroVersionBc = $daSoumissionBcRepository->getNumeroVersionMax($dto->numeroCde, $dto->codeSociete);
        $ddpDto->dateDemande = new DateTime();
        $ddpDto->numeroSoumissionDdpDa = $numeroSoumissionDdpDa;
        $ddpDto->numeroDemandeAppro = $infoDa['numeroDemandeAppro'];
        $ddpDto->numeroLivraison = $dto->numLiv;
        $ddpDto->motif = $infoDa['objetDal'];

        return $ddpDto;
    }

    private function getTypeDdp(DaSoumissionFacBlDto $dto): ?TypeDemande
    {
        $typeDemandeRepository = $this->em->getRepository(TypeDemande::class);
        $typeApresLivraison = $typeDemandeRepository->find(TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE);
        $typeRegule = $typeDemandeRepository->find(TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_REGULE);
        $typeBonAPayer = $typeDemandeRepository->find(TypeDemandePaiementConstants::ID_BON_A_PAYER);

        if ($dto->montantAregulariser <= 0.0) {
            return $typeRegule;
        } elseif ($dto->typeDdp === 'bap') {
            return $typeBonAPayer;
        }
        return $typeApresLivraison;
    }

    public function genererNumeroDdp(): string
    {
        return $this->numeroGenerateurService->genererNumeroDdp();
    }

    public function genererNumeroBap(): string
    {
        return $this->numeroGenerateurService->genererNumeroBap();
    }

    private function getNumFacEtMontant(string $numLiv, string $codeSociete): array
    {
        return $this->daSoumissionFacBlModel->getMontantReceptionIpsEtNumFac($numLiv, $codeSociete);
    }
}
