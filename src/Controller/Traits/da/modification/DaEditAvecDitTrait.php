<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DaObservationRepository;
use App\Service\da\FileUploaderForDAService;

trait DaEditAvecDitTrait
{
    use DaEditTrait;

    //==================================================================================================
    private DitRepository $ditRepository;
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
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

    private function modificationDAL(DemandeAppro $demandeAppro, $formDAL, string $statut): void
    {
        $em = $this->getEntityManager();
        $numeroDemandeAppro = $demandeAppro->getNumeroDemandeAppro();
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numeroDemandeAppro);

        // Indexation des DAL par numéro de ligne
        $dalParLigne = [];

        foreach ($formDAL as $subFormDAL) {
            /** 
             * @var DemandeApproL $demandeApproL
             * 
             * On récupère les données du formulaire DAL
             */
            $demandeApproL = $subFormDAL->getData();

            // Si demandeApproL à supprimer
            if ($demandeApproL->getDeleted() == 1) {
                $em->remove($demandeApproL);
                $this->deleteDALR($demandeApproL);
            } else {
                // Récupérer les données
                $filesToDelete = $subFormDAL->get('filesToDelete')->getData();
                $existingFileNames = $subFormDAL->get('existingFileNames')->getData();
                $newFiles = $subFormDAL->get('fileNames')->getData();

                // Supprimer les fichiers
                if ($filesToDelete) {
                    $this->daFileUploader->deleteFiles(
                        explode(',', $filesToDelete),
                        $numeroDemandeAppro
                    );
                }

                // Gérer l'upload et obtenir la liste finale
                $allFileNames = $this->daFileUploader->handleFileUpload(
                    $newFiles,
                    $existingFileNames,
                    $numeroDemandeAppro,
                    FileUploaderForDAService::FILE_TYPE["DEVIS"]
                );

                $demandeApproL
                    ->setNumeroDemandeAppro($numeroDemandeAppro)
                    ->setStatutDal($statut)
                    ->setNumeroVersion($numeroVersionMax)
                    ->setJoursDispo($this->getJoursRestants($demandeApproL))
                    ->setFileNames($allFileNames)
                ;

                $dalParLigne[$demandeApproL->getNumeroLigne()] = $demandeApproL;
                $em->persist($demandeApproL); // on persiste la DAL
            }
        }

        /** @var DemandeApproLR[] $dalrs */
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numeroDemandeAppro]);
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
