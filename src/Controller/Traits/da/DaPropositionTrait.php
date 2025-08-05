<?php

namespace App\Controller\Traits\da;

use App\Controller\Traits\EntityManagerAwareTrait;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\autres\VersionService;

trait DaPropositionTrait
{
    use DaTrait;
    use EntityManagerAwareTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;


    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionTrait(): void
    {
        $em = $this->getEntityManager();
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * Ajoute les données d'une Demande d'Achat dans la table `DaAfficher`, 
     * par le numéro de la Demande d'Achat.
     *
     * @param string $numDa  le numéro de la Demande d'Achat à traiter
     * @return void
     */
    public function ajouterDansTableAffichageParNumDa(string $numDa): void
    {
        $em = $this->getEntityManager();

        /** @var DemandeAppro $demandeAppro la DA correspondant au numero DA $numDa */
        $demandeAppro = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $numeroVersionMaxDaAfficher = $this->daAfficherRepository->getNumeroVersionMax($numDa);
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $donneesAfficher = $this->recuperationRectificationDonnee($numDa, $numeroVersionMax);
        foreach ($donneesAfficher as $donneeAfficher) {
            $daAfficher = new DaAfficher();
            if ($demandeAppro->getDit()) {
                $daAfficher->setDit($demandeAppro->getDit());
            }
            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->setNumeroVersion(VersionService::autoIncrement($numeroVersionMaxDaAfficher));
            if ($donneeAfficher instanceof DemandeApproL) {
                $daAfficher->enregistrerDal($donneeAfficher); // enregistrement pour DAL
            } else if ($donneeAfficher instanceof DemandeApproLR) {
                $daAfficher->enregistrerDalr($donneeAfficher); // enregistrement pour DALR
            }

            $em->persist($daAfficher);
        }
        $em->flush();
    }
}
