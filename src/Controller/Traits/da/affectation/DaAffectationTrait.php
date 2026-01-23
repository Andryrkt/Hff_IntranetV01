<?php

namespace App\Controller\Traits\da\affectation;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\da\DemandeApproParentRepository;

trait DaAffectationTrait
{
    use DaTrait;

    //=====================================================================================
    private DemandeApproParentRepository $demandeApproParentRepository;

    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaAffectationTrait(): void
    {
        $this->initDaTrait();
        $em = $this->getEntityManager();
        $this->demandeApproParentRepository = $em->getRepository(DemandeApproParent::class);
    }
    //=====================================================================================


    private function traitementDaParentLines(ArrayCollection $daParentLines, DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = $this->createDemandeAppro($daParent, $daType);

        $numLigne = 0;
        /** @var DemandeApproParentLine $daParentLine */
        foreach ($daParentLines as $daParentLine) {
            $demandeApproLine = new DemandeApproL();

            $demandeApproLine
                ->duplicateDaParentLine($daParentLine)
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setNumeroLigne(++$numLigne)
                ->setStatutDal($demandeAppro->getStatutDal())
                ->setEstValidee($demandeAppro->getEstValidee())
                ->setValidePar($demandeAppro->getValidePar())
            ;

            // ajouter dans la collection des DAL de la nouvelle DA
            $demandeAppro->addDAL($demandeApproLine);

            $this->getEntityManager()->persist($demandeApproLine);
        }
        $this->getEntityManager()->persist($demandeAppro);
    }

    private function createDemandeAppro(DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = new DemandeAppro();

        $prefix = [
            DemandeAppro::TYPE_DA_DIRECT           => 'DAPD',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'DAPP',
        ];

        $statut = [
            DemandeAppro::TYPE_DA_DIRECT           => DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => DemandeAppro::STATUT_VALIDE,
        ];

        $numDa = str_replace('DAP', $prefix[$daType], $daParent->getNumeroDemandeAppro());

        $demandeAppro
            ->duplicateDaParent($daParent)
            ->setDaTypeId($daType)
            ->setNumeroDemandeAppro($numDa)
            ->setStatutDal($statut[$daType])
        ;

        if ($daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL) {
            $demandeAppro
                ->setEstValidee(true)
                ->setValidateur($this->getUser())
                ->setValidePar($this->getUser()->getNomUtilisateur())
            ;
        }
        return $demandeAppro;
    }
}
