<?php

namespace App\Factory\da\CdeFrnDto;

use App\Constants\da\TypeDaConstants;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Dto\Da\ListeCdeFrn\DaDdpaDto;
use App\Dto\Da\ListeCdeFrn\DaSituationReceptionDto;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\admin\Agence;
use App\Entity\admin\Application;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\admin\Service;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\da\DemandeAppro;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Model\da\DaModel;
use App\Model\da\DaSoumissionFacBlModel;
use App\Model\ddp\DemandePaiementModel;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\autres\AutoIncDecService;
use App\Service\historiqueOperation\HistoriqueOperationDaFacBlService;
use App\Service\historiqueOperation\HistoriqueOperationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionFacBlFactory
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private EntityManagerInterface $em;
    private DemandeApproRepository $demandeApproRepository;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private HistoriqueOperationService $historiqueOperation;
    private DaModel $daModel;
    private DaSoumissionFacBlModel $daSoumissionFacBlModel;
    private DemandePaiementModel $ddpModel;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->daSoumissionFacBlRepository = $em->getRepository(DaSoumissionFacBl::class);
        $this->historiqueOperation = new HistoriqueOperationDaFacBlService($em);
        $this->daModel = new DaModel();
        $this->daSoumissionFacBlModel = new DaSoumissionFacBlModel();
        $this->ddpModel = new DemandePaiementModel();
    }

    public function initialisation(
        string $numCde,
        string $numDa,
        string $numOr,
        User $user
    ): DaSoumissionFacBlDto {
        $dto = new DaSoumissionFacBlDto();

        $dto->numeroCde = $numCde;
        $dto->utilisateur = $user->getNomUtilisateur();
        $dto->numeroVersionFacBl = $this->getNumeroVersion($numCde);
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroDemandeDit = $this->getNumeroDit($numDa);
        $dto->numeroOR = $numOr;
        $dto->dateBlFac = $this->getDateLivraisonPrevue($numDa, $numCde);

        // livraison ===========================
        $dto->infoLiv = $this->getInfoLivraison($numCde, $numDa);
        $dto->numLiv = array_keys($dto->infoLiv);

        // info commande (BC) ==========================
        $dto->infoBc = $this->getInfoBc($numCde);
        $dto->numeroFournisseur = $dto->infoBc['num_fournisseur'];
        $dto->nomFournisseur = $dto->infoBc['nom_fournisseur'];

        // DDPL ==========================
        $dto->statutFacBl = self::STATUT_SOUMISSION;
        $dto->totalMontantCommande = $this->getTotalMontantCommande($numCde);

        // recuperation des demandes de paiement déjà payer
        $this->getDdpa($numCde, $dto);

        $this->getMontant($numCde, $dto);

        // recupération des informations de commande
        $this->getReception($numCde, $dto);

        // recupération information de demande de paiement
        $this->getDemandePaiement($dto, $numCde, $user);

        return $dto;
    }

    public function EnrichissementDtoApresSoumission(DaSoumissionFacBlDto $dto, $nomPdfFusionner = null)
    {
        if (empty($nomPdfFusionner)) return;

        $dto->pieceJoint1 = $nomPdfFusionner;
        $dto->montantBlFacture = (float)str_replace(',', '.', str_replace(' ', '', $dto->montantBlFacture ?? '0'));
        $dto->numeroFactureFournisseur = $this->getNumFacEtMontant($dto->numLiv)[0]['numero_facture'];

        // Bon à payer (BAP) ===============================
        $dto->numeroBap = $this->genererNumeroBap();
        $dto->statutBap = 'A transmettre';
        $dto->dateStatutBap = new DateTime();
        $dto->montantReceptionIps = $this->getNumFacEtMontant($dto->numLiv)[0]['montant_reception_ips'];

        // livraison ===========================
        $dto->dateClotLiv = new DateTime($dto->infoLiv[$dto->numLiv]['date_clot']);
        $dto->refBlFac = $dto->infoLiv[$dto->numLiv]['ref_fac_bl'];

        return $dto;
    }

    private function getNumeroDit(string $numDa): ?string
    {
        return $this->demandeApproRepository->getNumDitDa($numDa);
    }

    private function getDateLivraisonPrevue(string $numDa, string $numCde): ?DateTime
    {
        $dateLivraisonPrevue = $this->daAfficherRepository->getDateLivraisonPrevue($numDa, $numCde);
        return $dateLivraisonPrevue ? new DateTime($dateLivraisonPrevue) : null;
    }

    private function getInfoLivraison(string $numCde, string $numDa): array
    {
        $infosLivraisons = (new DaModel)->getInfoLivraison($numCde);
        //$daSoumissionBcRepository = $this->em->getRepository(DaSoumissionBc::class);
        //$estDdpa = $daSoumissionBcRepository->getEstDdpAvance($numCde);

        if (empty($infosLivraisons)) {
            $message = "La commande n° <b>$numCde</b> n'a pas de livraison associé dans IPS. Merci de bien vérifier le numéro de la commande.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        $livraisonSoumis = $this->daSoumissionFacBlRepository->getAllLivraisonSoumis($numDa, $numCde);

        $statutBaps = $this->daSoumissionFacBlRepository->getStatutBap($numDa, $numCde);
        $demandePaiementRepository = $this->em->getRepository(DemandePaiement::class);
        $statutDdps  = $demandePaiementRepository->getStatutDdpSelonNumCde($numCde);
        $nombreLivraisonSoumis = $this->daSoumissionFacBlRepository->getNombreLivraisonSoumis($numDa, $numCde);
        if (!empty($livraisonSoumis) && !in_array('A transmettre', $statutBaps) && !in_array('Refusé', $statutDdps) && $nombreLivraisonSoumis === count($infosLivraisons)) {
            $message = "Les BAP sont toutes transmise et validé ou en cours de validation.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        foreach ($livraisonSoumis as $numLiv) {
            unset($infosLivraisons[$numLiv]); // exclure les livraisons déjà soumises
        }

        // if (empty($infosLivraisons)) {
        //     $message = "La commande n° <b>$numCde</b> n'a plus de livraison à soumettre. Toutes les livraisons associées ont déjà été soumises.";
        //     $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        // }

        return $infosLivraisons;
    }

    private function getInfoBc($numCde): array
    {
        return $this->daModel->getInfoBC($numCde);
    }

    private function EstDdpa($numCde): ?bool
    {
        $bcRepository = $this->em->getRepository(DaSoumissionBc::class);
        $numeroVersionMax = $bcRepository->getNumeroVersionMax($numCde);
        $bc =   $bcRepository->findOneBy(['numeroCde' => $numCde, 'numeroVersion' => $numeroVersionMax]);
        return $bc->getDemandePaiementAvance();
    }

    private function genererNumeroBap(): string
    {
        //recupereation de l'application BAP pour generer le numero de bap
        $application = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => 'BAP']);
        //generation du numero de bap
        $numeroBap = AutoIncDecService::autoGenerateNumero('BAP', $application->getDerniereId(), true);
        //mise a jour de la derniere id de l'application BAP
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->em, $numeroBap);
        return $numeroBap;
    }

    private function getNumFacEtMontant($numLiv): array
    {
        $daSoumissionFacBlModel = new DaSoumissionFacBlModel();
        return $daSoumissionFacBlModel->getMontantReceptionIpsEtNumFac($numLiv);
    }

    private function getNumeroVersion($numCde): int
    {
        $numeroVersionMax = $this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde);

        return AutoIncDecService::autoIncrement($numeroVersionMax);
    }

    /** DDPL */
    private function getTotalMontantCommande($numCde): float
    {
        $totalMontantCommande = $this->daSoumissionFacBlModel->getTotalMontantCommande($numCde);
        if ($totalMontantCommande) return (float)$totalMontantCommande[0];

        return 0;
    }

    public function getReception(int $numCde, $dto)
    {
        $articleCdes = $this->daSoumissionFacBlModel->getArticleCde($numCde);

        foreach ($articleCdes as $articleCde) {
            $situRecepDto = new DaSituationReceptionDto();
            $dto->receptions[] = DaSoumissionFacBlMapper::mapReception($situRecepDto, $articleCde);
        }
    }

    public function getDdpa(int $numCde, DaSoumissionFacBlDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $runningCumul = 0; // Variable pour maintenir le total cumulé

        foreach ($ddps as  $ddp) {
            // Crée un nouveau DTO pour chaque élément afin d'avoir des objets distincts
            $ddpaDto = new DaDdpaDto();

            // Copie les propriétés nécessaires du DTO initial qui sont communes à tous les éléments
            $ddpaDto->totalMontantCommande = $dto->totalMontantCommande;

            // Mappe l'entité vers le nouveau DTO (le mapper ne s'occupe plus du cumul)
            DaSoumissionFacBlMapper::mapDdp($ddpaDto, $ddp);

            // Calcule et définit la valeur cumulative ici dans la logique du contrôleur
            $runningCumul += $ddpaDto->ratio;
            $ddpaDto->cumul = $runningCumul;

            $dto->daDdpa[] = $ddpaDto;
        }

        return $dto;
    }

    public function getMontant(int $numCde, DaSoumissionFacBlDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $totalMontantPayer = $this->getTotalPayer($ddps);
        $ratioTotalPayer = ($totalMontantPayer / $dto->totalMontantCommande) * 100;
        $montantAregulariser = $dto->totalMontantCommande - $totalMontantPayer;
        $ratioMontantARegul = ($montantAregulariser /  $dto->totalMontantCommande) * 100;

        $dto = DaSoumissionFacBlMapper::mapTotalPayer($dto, $totalMontantPayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul);

        return $dto;
    }

    private function getTotalPayer(array $ddps): float
    {
        $montantpayer = 0;

        foreach ($ddps as $item) {
            $montantpayer = $montantpayer + $item->getMontantAPayers();
        }

        return $montantpayer;
    }

    private function getDemandePaiement(DaSoumissionFacBlDto $dto, int $numCde, User $user)
    {
        $typeDemandeRepository = $this->em->getRepository(TypeDemande::class);
        $daSoumissionBcRepository = $this->em->getRepository(DaSoumissionBc::class);
        $daAfficherRepository = $this->em->getRepository(DaAfficher::class);
        $infoDa = $daAfficherRepository->getInfoDa($numCde);
        $typeApresLivraison = $typeDemandeRepository->find(TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE);
        $typeRegule = $typeDemandeRepository->find(TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_REGULE);


        $infoFournisseur = $this->ddpModel->recupInfoPourDa($infoDa['numeroFournisseur'], $numCde);

        if (!empty($infoFournisseur)) {
            $dto->numeroFournisseur = $infoFournisseur[0]['num_fournisseur'];
            $dto->ribFournisseur = $infoFournisseur[0]['rib_fournisseur'];
            $dto->beneficiaire = $infoFournisseur[0]['nom_fournisseur']; // nom du fournisseur
            $dto->modePaiement = $infoFournisseur[0]['mode_paiement'];
            $dto->devise = $infoFournisseur[0]['devise'];
        }

        $dto->numeroDdp = $this->numeroDdp();
        $dto->debiteur = $this->debiteur($infoDa['daTypeId'], $infoDa);
        $dto->typeDemande = $dto->montantAregulariser === 0 ? $typeRegule : $typeApresLivraison;
        $dto->statut = 'Soumis à validation';
        $dto->demandeur = $user->getNomUtilisateur();
        $dto->adresseMailDemandeur = $user->getMail();
        $dto->montantAPayer = $dto->montantAregulariser;
        $dto->numeroCommande = [$numCde];
        $dto->appro = true;
        $dto->typeDa = $infoDa['daTypeId'];
        $dto->numeroVersionBc = $daSoumissionBcRepository->getNumeroVersionMax($numCde);
        $dto->dateCreation = new DateTime();
    }

    private function numeroDdp(): string
    {
        //recupereation de l'application DDP pour generer le numero de ddp
        $application = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP']);
        if (!$application) {
            throw new \Exception("L'application 'DDP' n'a pas été trouvée dans la configuration.");
        }
        //generation du numero de ddp
        $numeroDdp = AutoIncDecService::autoGenerateNumero('DDP', $application->getDerniereId(), true);
        //mise a jour de la derniere id de l'application DDP
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->em, $numeroDdp);
        return $numeroDdp;
    }

    private function debiteur(int $typeDa, array $infoDa): array
    {
        $agenceRepository = $this->em->getRepository(Agence::class);
        $serviceRepository = $this->em->getRepository(Service::class);
        if ($typeDa === TypeDaConstants::TYPE_DA_AVEC_DIT) {
            $codeAgenceServiceIps = $this->ddpModel->getCodeAgenceService($infoDa['numeroOr']);
            $debiteur = [
                'agence' => $agenceRepository->findOneBy(['codeAgence' => $codeAgenceServiceIps[0]['code_agence']]),
                'service' => $serviceRepository->findOneBy(['codeService' => $codeAgenceServiceIps[0]['code_service']])
            ];
        } elseif ($typeDa === TypeDaConstants::TYPE_DA_DIRECT) {
            $debiteur = [
                'agence' => $agenceRepository->find($infoDa['agenceDebiteur']),
                'service' => $serviceRepository->find($infoDa['serviceDebiteur'])
            ];
        }

        return $debiteur;
    }
}
