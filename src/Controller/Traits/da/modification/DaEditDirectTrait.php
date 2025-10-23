<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaObservationRepository;

trait DaEditDirectTrait
{
    use DaEditTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

    private function filtreDal($demandeAppro, int $numeroVersionMax): DemandeAppro
    {
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

        // Indexation des DAL par numéro de ligne
        $dalParLigne = [];
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
                $dalParLigne[$demandeApproL->getNumeroLigne()] = $demandeApproL;
                $em->persist($demandeApproL); // on persiste la DAL
            }
        }
        /** @var DemandeApproLR[] $dalrs */
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $demandeAppro->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            $ligneDAL = $dalParLigne[$dalr->getNumeroLigne()];
            $dalr
                ->setStatutDal($statut)
                ->setDateFinSouhaite($ligneDAL->getDateFinSouhaite())
                ->setQteDem($ligneDAL->getQteDem())
            ;
            $em->persist($dalr);
        }
    }
}
