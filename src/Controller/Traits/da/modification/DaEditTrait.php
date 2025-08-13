<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\da\DaTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DaEditTrait
{
    use DaTrait;

    /** 
     * Fonction pour obtenir les anciens DAL
     */
    private function getAncienDAL(DemandeAppro $demandeAppro): array
    {
        $result = [];
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $result[] = clone $demandeApproL;
        }
        return $result;
    }

    /**
     * Suppression physique des DALR correspondant au DAL $dal
     *
     * @param DemandeApproL $dal
     * @return void
     */
    private function deleteDALR(DemandeApproL $dal)
    {
        $em = $this->getEntityManager();
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigne' => $dal->getNumeroLigne(), 'numeroDemandeAppro' => $dal->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            $em->remove($dalr);
        }
    }

    private function PeutModifier($demandeAppro)
    {
        return ($this->estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == DemandeAppro::STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == DemandeAppro::STATUT_VALIDE));
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

    /** 
     * Traitement des fichiers
     */
    private function traitementFichiers(DemandeApproL $dal, $files)
    {
        if ($files !== []) {
            $fileNames = [];
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->daFileUploader->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
            $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
        }
    }
}
