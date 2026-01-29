<?php

namespace App\Factory\ddp;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Application;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Constants\da\TypeDaConstants;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DaAfficher;
use App\Model\ddp\DemandePaiementModel;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\autres\AutoIncDecService;
use App\Service\ddp\DocDemandePaiementService;

class DemandePaiementFactory
{
    private $em;
    private DemandePaiementModel $ddpModel;
    private DocDemandePaiementService $docDemandePaiementService;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->ddpModel  = new DemandePaiementModel();
        $this->docDemandePaiementService = new DocDemandePaiementService($em);
    }

    public function load(int $typeDdp, ?int $numCdeDa, ?int $typeDa, ?string $numDdp, User $user): DemandePaiementDto
    {
        $typeDemandeRepository = $this->em->getRepository(TypeDemande::class);
        $DaAfficherRepository = $this->em->getRepository(DaAfficher::class);
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);


        $infoDa = $DaAfficherRepository->getInfoDa($numCdeDa);


        $dto = new DemandePaiementDto();
        $dto->typeDemande = $typeDemandeRepository->find($typeDdp);
        $dto->numeroFacture = []; // TODO: mbola anontaniana
        $dto->numeroCommande = [$numCdeDa];
        $dto->debiteur = $this->debiteur($typeDa, $infoDa);

        // Pour le DA =====================================
        $dto->typeDa = $typeDa;
        $recupMontantTotal = $this->ddpModel->getMontantTotalCde($numCdeDa);
        if (empty($recupMontantTotal)) {
            throw new \Exception("Montant total introuvable pour le numero commande $numCdeDa");
        }

        $dto->montantTotalCde = (float)$recupMontantTotal[0];
        $dto->montantDejaPaye = $ddpRepository->getMontantDejaPayer($numCdeDa);
        $dto->montantRestantApayer = $dto->montantTotalCde - $dto->montantDejaPaye;
        $dto->pourcentageAvance = (($dto->montantDejaPaye + $dto->montantAPayer) / $dto->montantTotalCde) * 100 . ' %';
        $dto->montantAPayer = $dto->montantRestantApayer;
        $dto->pourcentageAPayer = (int)(($dto->montantAPayer / $dto->montantTotalCde) * 100);
        $dto->numeroDa = $infoDa['numeroDemandeAppro'];
        // recupération des fichiers de devis de la DA
        $dto->fichiersChoisis = $this->recupFichierDevisDa($dto);

        // info generale =====================
        $dto->demandeur = $user->getNomUtilisateur();
        $dto->adresseMailDemandeur = $user->getMail();
        $dto->statut = 'Soumis à validation';
        $dto->appro = $typeDa !== null ? true : false;
        $dto->numeroDdp = $typeDa !== null ? $numDdp : $this->numeroDdp();
        $dto->numeroVersion = 1;
        $dto->numeroDossierDouane = $this->docDemandePaiementService->recupNumDossierDouane($dto);
        $dto->dateDemande = new \DateTime();

        // fournisseur ======================
        $infoFournisseur = $this->ddpModel->recupInfoPourDa($infoDa['numeroFournisseur'], $numCdeDa);

        if (!empty($infoFournisseur)) {
            $dto->numeroFournisseur = $infoFournisseur[0]['num_fournisseur'];
            $dto->ribFournisseur = $infoFournisseur[0]['rib_fournisseur'];
            $dto->ribFournisseurAncien = $infoFournisseur[0]['rib_fournisseur'];
            $dto->beneficiaire = $infoFournisseur[0]['nom_fournisseur']; // nom du fournisseur
            $dto->modePaiement = $infoFournisseur[0]['mode_paiement'];
            $dto->devise = $infoFournisseur[0]['devise'];
        }

        return $dto;
    }

    private function recupFichierDevisDa(DemandePaiementDto $dto)
    {
        $listeFichiersPJ = [];
        $path = $_ENV['BASE_PATH_FICHIER'] . 'da/' . $dto->numeroDa . '/';
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^(_pj_|PJ_|devis_pj_)/', $file)) {
                    $listeFichiersPJ[] = $file;
                }
            }
        }
        return $listeFichiersPJ;
    }

    private function debiteur(int $typeDa, array $infoDa): array
    {
        $codeAgenceServiceIps = $this->ddpModel->getCodeAgenceService($infoDa['numeroOr']);

        $agenceRepository = $this->em->getRepository(Agence::class);
        $serviceRepository = $this->em->getRepository(Service::class);

        if ($typeDa === TypeDaConstants::TYPE_DA_AVEC_DIT) {
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
}
