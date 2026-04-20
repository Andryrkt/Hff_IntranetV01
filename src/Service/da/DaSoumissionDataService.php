<?php

namespace App\Service\da;

use App\Constants\da\TypeDaConstants;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\da\DemandeAppro;
use App\Model\da\DaModel;
use App\Model\ddp\DemandePaiementModel;
use App\Service\autres\AutoIncDecService;
use App\Service\historiqueOperation\HistoriqueOperationDaFacBlService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionDataService
{
    private EntityManagerInterface $em;
    private DaModel $daModel;
    private DemandePaiementModel $ddpModel;
    private HistoriqueOperationDaFacBlService $historiqueOperation;

    public function __construct(
        EntityManagerInterface $em,
        DaModel $daModel,
        DemandePaiementModel $ddpModel,
        HistoriqueOperationDaFacBlService $historiqueOperation
    ) {
        $this->em = $em;
        $this->daModel = $daModel;
        $this->ddpModel = $ddpModel;
        $this->historiqueOperation = $historiqueOperation;
    }

    public function getNumeroDit(string $numDa): ?string
    {
        return $this->em->getRepository(DemandeAppro::class)->getNumDitDa($numDa);
    }

    public function getDateLivraisonPrevue(string $numDa, string $numCde): ?DateTime
    {
        $date = $this->em->getRepository(DaAfficher::class)->getDateLivraisonPrevue($numDa, $numCde);
        return $date ? new DateTime($date) : null;
    }

    public function getInfoLivraison(string $numCde, string $numDa): array
    {
        $infosLivraisons = $this->daModel->getInfoLivraison($numCde);

        if (empty($infosLivraisons)) {
            $message = "La commande n° <b>$numCde</b> n'a pas de livraison associé dans IPS. Merci de bien vérifier le numéro de la commande.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        $livraisonSoumis = $this->em->getRepository(DaSoumissionFacBl::class)->getAllLivraisonSoumis($numDa, $numCde);

        foreach ($livraisonSoumis as $numLiv) {
            unset($infosLivraisons[$numLiv]);
        }

        if (empty($infosLivraisons)) {
            $message = "Toutes les livraisons sont en cours de validation ou déjà validées.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        return $infosLivraisons;
    }

    public function getInfoDa(string $numCde): array
    {
        return $this->em->getRepository(DaAfficher::class)->getInfoDa($numCde);
    }

    public function getInfoBc(string $numCde): array
    {
        return $this->daModel->getInfoBC($numCde);
    }

    public function getNumeroVersion(string $numCde): int
    {
        $numeroVersionMax = $this->em->getRepository(DaSoumissionFacBl::class)->getNumeroVersionMax($numCde);
        return AutoIncDecService::autoIncrement($numeroVersionMax);
    }

    public function getInfoFournisseur(string $numFrn, string $numCde): array
    {
        return $this->ddpModel->recupInfoPourDa($numFrn, $numCde);
    }

    public function resolveDebiteur(int $typeDa, array $infoDa): array
    {
        $agenceRepository = $this->em->getRepository(Agence::class);
        $serviceRepository = $this->em->getRepository(Service::class);

        if ($typeDa === TypeDaConstants::TYPE_DA_AVEC_DIT) {
            $codeAgenceServiceIps = $this->ddpModel->getCodeAgenceService($infoDa['numeroOr']);
            return [
                'agence' => $agenceRepository->findOneBy(['codeAgence' => $codeAgenceServiceIps[0]['code_agence']]),
                'service' => $serviceRepository->findOneBy(['codeService' => $codeAgenceServiceIps[0]['code_service']])
            ];
        }

        if ($typeDa === TypeDaConstants::TYPE_DA_DIRECT) {
            return [
                'agence' => $agenceRepository->find($infoDa['agenceDebiteur']),
                'service' => $serviceRepository->find($infoDa['serviceDebiteur'])
            ];
        }

        return ['agence' => null, 'service' => null];
    }
}
