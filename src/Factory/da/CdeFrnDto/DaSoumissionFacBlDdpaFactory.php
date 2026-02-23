<?php

namespace App\Factory\da\CdeFrnDto;

use App\Constants\da\TypeDaConstants;
use App\Dto\Da\ListeCdeFrn\DaDdpaDto;
use App\Dto\Da\ListeCdeFrn\DaSituationReceptionDto;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
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
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlDdpaMapper;
use App\Model\da\DaSoumissionFacBlDdpaModel;
use App\Model\ddp\DemandePaiementModel;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\autres\AutoIncDecService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionFacBlDdpaFactory
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private EntityManagerInterface $em;

    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private DaSoumissionFacBlDdpaModel $daSoumissionFacBlDdpaModel;
    private DemandeApproRepository $demandeApproRepository;
    private DemandePaiementModel $ddpModel;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->daSoumissionFacBlRepository = $em->getRepository(DaSoumissionFacBl::class);
        $this->daSoumissionFacBlDdpaModel = new DaSoumissionFacBlDdpaModel();
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->ddpModel = new DemandePaiementModel();
    }

    public function initialisation($numCde, $numDa, $numOR,  User $user): DaSoumissionFacBlDdpaDto
    {
        $dto = new DaSoumissionFacBlDdpaDto();
        $dto->numeroCde = $numCde;
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroOR = $numOR;
        $dto->statutFacBl = self::STATUT_SOUMISSION;
        $dto->numeroDemandeDit = $this->getNumeroDit($numDa);
        $dto->utilisateur = $user->getNomUtilisateur();
        $dto->numeroVersionFacBl = $this->getNumeroVersion($numCde);
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

    private function getNumeroDit(string $numDa): ?string
    {
        return $this->demandeApproRepository->getNumDitDa($numDa);
    }

    public function enrichissementDtoApresSoumission(DaSoumissionFacBlDdpaDto $dto, $nomPdfFusionner = null)
    {
        if (empty($nomPdfFusionner)) return;

        $dto->pieceJoint1 = $nomPdfFusionner;
        $dto->nomAvecCheminFichierDistant = $this->getNomAvecCheminDistant($dto);

        return $dto;
    }

    private function getNumeroVersion($numCde): int
    {
        $numeroVersionMax = $this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde);

        return AutoIncDecService::autoIncrement($numeroVersionMax);
    }

    private function getTotalMontantCommande($numCde): float
    {
        $totalMontantCommande = $this->daSoumissionFacBlDdpaModel->getTotalMontantCommande($numCde);
        if ($totalMontantCommande) return (float)$totalMontantCommande[0];

        return 0;
    }

    public function getReception(int $numCde, $dto)
    {
        $articleCdes = $this->daSoumissionFacBlDdpaModel->getArticleCde($numCde);

        foreach ($articleCdes as $articleCde) {
            $situRecepDto = new DaSituationReceptionDto();
            $dto->receptions[] = DaSoumissionFacBlDdpaMapper::mapReception($situRecepDto, $articleCde);
        }
    }

    public function getDdpa(int $numCde, DaSoumissionFacBlDdpaDto $dto)
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
            DaSoumissionFacBlDdpaMapper::mapDdp($ddpaDto, $ddp);

            // Calcule et définit la valeur cumulative ici dans la logique du contrôleur
            $runningCumul += $ddpaDto->ratio;
            $ddpaDto->cumul = $runningCumul;

            $dto->daDdpa[] = $ddpaDto;
        }

        return $dto;
    }

    public function getMontant(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $totalMontantPayer = $this->getTotalPayer($ddps);
        $ratioTotalPayer = ($totalMontantPayer / $dto->totalMontantCommande) * 100;
        $montantAregulariser = $dto->totalMontantCommande - $totalMontantPayer;
        $ratioMontantARegul = ($montantAregulariser /  $dto->totalMontantCommande) * 100;

        $dto = DaSoumissionFacBlDdpaMapper::mapTotalPayer($dto, $totalMontantPayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul);

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

    private function getDemandePaiement(DaSoumissionFacBlDdpaDto $dto, int $numCde, User $user)
    {
        $typeDemandeRepository = $this->em->getRepository(TypeDemande::class);
        $daSoumissionBcRepository = $this->em->getRepository(DaSoumissionBc::class);
        $daAfficherRepository = $this->em->getRepository(DaAfficher::class);
        $infoDa = $daAfficherRepository->getInfoDa($numCde);


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
        $dto->typeDemande = $typeDemandeRepository->find(2);
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

    private function getNomAvecCheminDistant(DaSoumissionFacBlDdpaDto $dto)
    {
        $basePathFichierCourt = $_ENV['BASE_PATH_FICHIER_COURT'];
        $numeroDdp = $dto->numeroDdp;
        $nomFichierDdp = $dto->numeroDdp . 'pdf';
        $nomFichierAvecCheminDistant = "\\\\192.168.0.28\c$\wamp64\www{$basePathFichierCourt}ddp\\{$numeroDdp}_New_1\\{$nomFichierDdp}";

        return $nomFichierAvecCheminDistant;
    }
}
