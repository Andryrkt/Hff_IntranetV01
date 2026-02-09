<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\da\FileUploaderForDAService;
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
