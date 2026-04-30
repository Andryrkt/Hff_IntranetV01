<?php

namespace App\Factory\ddp;

use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Dto\ddp\DdpDto;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dw\DwCommande;
use App\Model\ddp\DdpModel;
use App\Repository\dw\DwCommandeRepository;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;

class DdpFactory
{
    private DdpModel $ddpModel;
    private EntityManagerInterface $em;

    public function __construct(
        DdpModel $ddpModel,
        EntityManagerInterface $em
    ) {
        $this->ddpModel = $ddpModel;
        $this->em = $em;
    }

    public function initialisation(int $idTypeDdp): DdpDto
    {
        $dto = new DdpDto();

        $dto->typeDdp = $this->getTypeDdp($idTypeDdp);

        // initialisation formulaire
        $dto->choiceModePaiement = $this->modePaiement();
        $dto->choiceDevise = $this->devise();
        $dto->numeroCommande = $this->numeroCmd($idTypeDdp);
        $dto->numeroFacture = [];

        // Agence et Service par défaut
        $dto->debiteur = [
            'agence' => $this->em->getRepository(Agence::class)->find(1),
            'service' => $this->em->getRepository(Service::class)->find(1)
        ];



        return $dto;
    }

    /**
     * TODO: encore à reflechire
     * Récupération des numéros de facture
     * 
     * @param string $numeroFournisseur
     * @param int $typeId
     * @return array
     */
    private function numeroFac(string $numeroFournisseur, int $typeId): array
    {
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

        $listeGcot = $this->ddpModel->finListFacGcot($numeroFournisseur, $numCdesString);
        return array_combine($listeGcot, $listeGcot);
    }

    /**
     * Récupération des numéros de commande
     * 
     * @param int $typeId
     * @return array
     */
    private function numeroCmd(int $typeId): array
    {
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        return array_combine($numCdes, $numCdes);
    }

    /**
     * Récupération des numéros de commande 
     * facturé et non facturé
     * selon le type de demande
     * 
     * @param int $typeId
     * @return array
     */
    private function recuperationCdeFacEtNonFac(int $typeId): array
    {
        /** @var ?DwCommandeRepository $dwCommandeRepo  */
        $dwCommandeRepo = $this->em->getRepository(DwCommande::class);
        $numCdeDws = $dwCommandeRepo->findNumCdeDw();
        $numCdes1 = [];
        $numCdes2 = [];
        foreach ($numCdeDws as $numCdeDw) {
            $numfactures = $this->ddpModel->cdeFacOuNonFac($numCdeDw);
            if (!empty($numfactures)) {
                $numCdes2[] = $numCdeDw;
            } else {
                $numCdes1[] = $numCdeDw;
            }
        }

        $numCdes = [];
        if ($typeId == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $numCdes = $numCdes2;
        } else {
            $numCdes = $numCdes1;
        }
        return $numCdes;
    }

    /**
     * Récupération du type de demande par id
     * 
     * @param int $typeDdp
     * @return TypeDemande
     */
    private function getTypeDdp(int $typeDdp): TypeDemande
    {
        return $this->em->getRepository(TypeDemande::class)->find($typeDdp);
    }

    /**
     * Récupération des modes de paiement
     * 
     * @return array
     */
    private function modePaiement(): array
    {
        $modePaiement = $this->ddpModel->getModePaiement();
        return array_combine($modePaiement, $modePaiement);
    }

    /**
     * Récupération des devises
     * 
     * @return array
     */
    private function devise(): array
    {
        $devisess = $this->ddpModel->getDevise();

        $devises = [
            '' => '',
        ];

        foreach ($devisess as $devise) {
            $devises[$devise['adevlib']] = $devise['adevcode'];
        }

        return $devises;
    }
}
