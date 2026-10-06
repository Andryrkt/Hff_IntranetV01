<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionBc;
use App\Service\genererPdf\GeneratePdf;
use Doctrine\ORM\EntityManagerInterface;

class DdpaDaService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function modificationtableDaSoumissionBc(DemandePaiementDto $dto): DdpaDaService
    {
        $daSoumisionBcRepository = $this->em->getRepository(DaSoumissionBc::class);
        $daSoumissionBc = $daSoumisionBcRepository
            ->findOneBy([
                'numeroCde' => $dto->numeroCommande[0],
                'numeroVersion' => $dto->numeroVersionBc
            ]);
        if ($daSoumissionBc) {
            $daSoumissionBc
                ->setDemandePaiementAvance($dto->ddpaDa)
                ->setNumerodemandePaiement($dto->numeroDdp)
            ;

            $this->em->persist($daSoumissionBc);
            $this->em->flush();
        }

        return $this;
    }

    public function copieBcDansDw(DemandePaiementDto $dto): DdpaDaService
    {
        $generatePdf = new GeneratePdf();
        /** COPIER DANS DW */
        $generatePdf->copyToDWBcDa($dto->nomPdfFusionnerBc, $dto->numeroDa);

        return $this;
    }

    public function modificationStatutBcDansDaAfficher(DemandePaiementDto $dto): DdpaDaService
    {
        $daAfficherRepository = $this->em->getRepository(DaAfficher::class);
        $daAffichers = $daAfficherRepository
            ->findBy([
                'numeroDemandeAppro' => $dto->numeroDa,
                'numeroVersion' => $dto->numeroVersionBc,
                'numeroCde' => $dto->numeroCommande[0]
            ]);
        if (!empty($daAffichers)) {
            foreach ($daAffichers as $daAfficher) {
                $daAfficher
                    ->setStatutCde(DaSoumissionBc::STATUT_SOUMISSION);
                $this->em->persist($daAfficher);
            }

            $this->em->flush();
        }

        return $this;
    }
}
