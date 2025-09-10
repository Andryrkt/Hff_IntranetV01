<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DaObservationRepository;

trait DaEditAvecDitTrait
{
    use DaEditTrait;

    //==================================================================================================
    protected DitRepository $ditRepository;
    protected DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

    private function filtreDal($demandeAppro, $dit, int $numeroVersionMax): DemandeAppro
    {
        $demandeAppro->setDit($dit); // association de la DA avec le DIT

        // filtre une collection de versions selon le numero de version max

        $dernieresVersions = $demandeAppro->getDAL()->filter(function ($item) use ($numeroVersionMax) {
            return $item->getNumeroVersion() == $numeroVersionMax && $item->getDeleted() == 0;
        });
        $demandeAppro->setDAL($dernieresVersions); // on remplace la collection de versions par la collection filtrée

        return $demandeAppro;
    }


    private function modificationDa(DemandeAppro $demandeAppro, $formDAL, string $statut): void
    {
        $em = $this->getEntityManager();
        $demandeAppro->setStatutDal($statut);
        $em->persist($demandeAppro); // on persiste la DA
        $this->modificationDAL($demandeAppro, $formDAL, $statut);
        $em->flush(); // on enregistre les modifications
    }

    private function modificationDAL($demandeAppro, $formDAL, string $statut): void
    {
        $em = $this->getEntityManager();
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        foreach ($formDAL as $subFormDAL) {
            /** 
             * @var DemandeApproL $demandeApproL
             * 
             * On récupère les données du formulaire DAL
             */
            $demandeApproL = $subFormDAL->getData();
            $files = $subFormDAL->get('fileNames')->getData(); // Récupération des fichiers

            $demandeApproL
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setStatutDal($statut)
                ->setNumeroVersion($numeroVersionMax)
                ->setJoursDispo($this->getJoursRestants($demandeApproL))
            ; // Incrémenter le numéro de version
            $this->traitementFichiers($demandeApproL, $files); // Traitement des fichiers uploadés

            if ($demandeApproL->getDeleted() == 1) {
                $em->remove($demandeApproL);
                $this->deleteDALR($demandeApproL);
            } else {
                $em->persist($demandeApproL); // on persiste la DAL
            }
        }
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $demandeAppro->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            $dalr->setStatutDal($statut);
            $em->persist($dalr);
        }
    }
}
