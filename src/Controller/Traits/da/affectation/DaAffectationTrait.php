<?php

namespace App\Controller\Traits\da\affectation;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Entity\da\DaSoumisAValidation;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use App\Repository\da\DaSoumisAValidationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\da\DemandeApproParentRepository;
use App\Service\autres\VersionService;
use Doctrine\ORM\EntityManagerInterface;

trait DaAffectationTrait
{
    use DaAfficherTrait;

    //=====================================================================================
    private EntityManagerInterface $em;
    private DemandeApproParentRepository $demandeApproParentRepository;
    private DaSoumisAValidationRepository $daSoumisAValidationRepository;
    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaAffectationTrait(): void
    {
        $this->initDaTrait();
        $this->em = $this->getEntityManager();
        $this->demandeApproParentRepository = $this->em->getRepository(DemandeApproParent::class);
        $this->daSoumisAValidationRepository = $this->em->getRepository(DaSoumisAValidation::class);
    }
    //=====================================================================================

    /**
     * Traite les lignes d'une demande parent
     *
     * @param ArrayCollection    $daParentLines  Collection des lignes de la demande parent
     * @param DemandeApproParent $daParent       Objet de la demande parent
     * @param int                $daType         Type de la demande
     */
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

            $this->em->persist($demandeApproLine);
        }
        $this->em->persist($demandeAppro);
        $this->em->flush();

        $validationDA = $daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL;
        $statutDW = $validationDA ? DemandeAppro::STATUT_DW_A_VALIDE : '';

        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro(), $validationDA, $statutDW);

        if ($validationDA) $this->ajouterDansDaSoumisAValidation($demandeAppro->getNumeroDemandeAppro(), $demandeAppro->getDemandeur());
    }

    /**
     * Crée une DA à partir d'une DA parent et du type de DA
     *
     * @param DemandeApproParent $daParent Objet de la demande parent
     * @param int                $daType   Type de la demande
     *
     * @return DemandeAppro
     */
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

    /**
     * Ajoute les données d'une Demande de Réappro dans la table `DaSoumisAValidation`
     *
     * @param string $numeroDemandeAppro  Numéro de la demande de réappro à traiter
     * @param string $demandeur           Demandeur de la demande de réappro
     */
    private function ajouterDansDaSoumisAValidation(string $numeroDemandeAppro, string $demandeur): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($numeroDemandeAppro);
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($numeroDemandeAppro)
            ->setNumeroVersion($numeroVersion)
            ->setStatut(DemandeAppro::STATUT_DW_A_VALIDE)
            ->setUtilisateur($demandeur)
        ;

        $this->em->persist($daSoumisAValidation);
        $this->em->flush();
    }
}
