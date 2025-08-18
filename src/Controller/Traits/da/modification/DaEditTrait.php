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
