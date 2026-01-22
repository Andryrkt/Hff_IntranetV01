<?php

namespace App\Factory\ddp;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Application;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Entity\da\DaSoumissionFacBl;
use App\Constants\da\TypeDaConstants;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\admin\utilisateur\User;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\autres\AutoIncDecService;

class DemandePaiementFactory
{
    private $em;
    private DemandePaiementModel $ddpModel;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->ddpModel  = new DemandePaiementModel();
    }

    public function load(int $typeDdp, ?int $numCdeDa, ?int $typeDa, User $user): DemandePaiementDto
    {
        $typeDemandeRepository = $this->em->getRepository(TypeDemande::class);
        $DaSoumissionFacBlRepository = $this->em->getRepository(DaSoumissionFacBl::class);
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);


        $infoDa = $DaSoumissionFacBlRepository->getInfoDa($numCdeDa);
        $codeAgenceServiceIps = $this->ddpModel->getCodeAgenceService($infoDa['numeroOR']);

        $dto = new DemandePaiementDto();
        $dto->typeDemande = $typeDemandeRepository->find($typeDdp);
        $dto->numeroFacture = [trim($infoDa['NumeroFactureFournisseur'])];
        $dto->numeroCommande = [$numCdeDa];
        $dto->debiteur = $this->debiteur($typeDa, $infoDa, $codeAgenceServiceIps);
        $dto->typeDa = $typeDa;

        $dto->montantTotalCde = $this->ddpModel->getMontantTotalCde($numCdeDa);
        $dto->montantDejaPaye = $ddpRepository->getMontantDejaPayer($numCdeDa);
        $dto->montantRestantApayer = $dto->montantTotalCde - $dto->montantDejaPaye;
        $dto->poucentageAvance = (($dto->montantDejaPaye + $dto->montantAPayer) / $dto->montantTotalCde) * 100 . ' %';

        $dto->demandeur = $user->getNomUtilisateur();
        $dto->adresseMailDemandeur = $user->getMail();
        $dto->statut = 'Soumis à validation';
        $dto->appro = $typeDa ? true : false;
        $dto->numeroDdp = $this->numeroDdp();
        $dto->numeroVersion = 1;
        $dto->numeroDossierDouane = $this->recupNumDossierDouane($dto);


        return $dto;
    }

    private function debiteur(int $typeDa, array $infoDa, array $codeAgenceServiceIps): array
    {
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

    /**
     * Récupération de numero de dossier de douane
     *
     * @param DemandePaiement $data
     * @return array
     */
    private function recupNumDossierDouane(DemandePaiementDto $dto): array
    {
        $numFrs = $dto->numeroFournisseur;
        $numCde = $dto->numeroCommande;
        $numFactures = $dto->numeroFacture;

        $numCdesString = TableauEnStringService::TableauEnString(',', $numCde);
        $numFactString = TableauEnStringService::TableauEnString(',', $numFactures);

        $numDossiers = array_column($this->ddpModel->getNumDossierGcot($numFrs, $numCdesString, $numFactString), 'Numero_Dossier_Douane');

        return $numDossiers;
    }
}
