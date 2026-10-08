<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use Doctrine\ORM\EntityManagerInterface;

class DaEditionService
{
    private EntityManagerInterface $em;
    private DaService $daService;
    private FileUploaderForDAService $daFileUploader;

    public function __construct(EntityManagerInterface $em, DaService $daService, FileUploaderForDAService $daFileUploader)
    {
        $this->em = $em;
        $this->daService = $daService;
        $this->daFileUploader = $daFileUploader;
    }

    /** Copie des DAL avant modification (pour l'e-mail de modification) */
    public function getAncienDAL(DemandeAppro $demandeAppro): array
    {
        $result = [];
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $result[] = clone $demandeApproL;
        }
        return $result;
    }

    public function peutModifier(string $statutDa, bool $profil): bool
    {
        $statutModifiable = in_array($statutDa, [StatutDaConstant::STATUT_SOUMIS_APPRO, StatutDaConstant::STATUT_VALIDE, StatutDaConstant::STATUT_AUTORISER_EMETTEUR]);
        return $statutModifiable && $profil;
    }

    /**
     * Enregistre la modification d'une DA et de ses lignes.
     *
     * @param array[] $lignes une entrée par ligne du formulaire :
     *                        ['dal' => DemandeApproL, 'filesToDelete' => ?string, 'existingFileNames' => mixed, 'newFiles' => mixed]
     */
    public function modificationDa(DemandeAppro $demandeAppro, iterable $lignes, string $statut): void
    {
        $demandeAppro->setStatutDal($statut);
        $this->em->persist($demandeAppro);
        $this->modificationDAL($demandeAppro, $lignes, $statut);
        $this->em->flush();
    }

    private function modificationDAL(DemandeAppro $demandeAppro, iterable $lignes, string $statut): void
    {
        $numeroDemandeAppro = $demandeAppro->getNumeroDemandeAppro();

        // Indexation des DAL par numéro de ligne
        $dalParLigne = [];

        foreach ($lignes as $ligne) {
            /** @var DemandeApproL $demandeApproL */
            $demandeApproL = $ligne['dal'];

            // Si demandeApproL à supprimer
            if ($demandeApproL->getDeleted() == 1) {
                $this->em->remove($demandeApproL);
                $this->deleteDALR($demandeApproL);
            } else {
                // Supprimer les fichiers
                if ($ligne['filesToDelete']) {
                    $this->daFileUploader->deleteFiles(explode(',', $ligne['filesToDelete']), $numeroDemandeAppro);
                }

                // Gérer l'upload et obtenir la liste finale
                $allFileNames = $this->daFileUploader->handleFileUpload(
                    $ligne['newFiles'],
                    $ligne['existingFileNames'],
                    $numeroDemandeAppro,
                    FileUploaderForDAService::FILE_TYPE["DEVIS"]
                );

                $demandeApproL
                    ->setNumeroDemandeAppro($numeroDemandeAppro)
                    ->setStatutDal($statut)
                    ->setJoursDispo($this->daService->getJoursRestants($demandeApproL))
                    ->setFileNames($allFileNames)
                ;

                $dalParLigne[$demandeApproL->getNumeroLigne()] = $demandeApproL;
                $this->em->persist($demandeApproL);
            }
        }

        /** @var DemandeApproLR[] $dalrs */
        $dalrs = $this->em->getRepository(DemandeApproLR::class)->findBy(['numeroDemandeAppro' => $numeroDemandeAppro]);
        foreach ($dalrs as $dalr) {
            $ligneDAL = $dalParLigne[$dalr->getNumeroLigne()];
            $dalr
                ->setStatutDal($statut)
                ->setDateFinSouhaite($ligneDAL->getDateFinSouhaite())
                ->setQteDem($ligneDAL->getQteDem())
            ;
            $this->em->persist($dalr);
        }
    }

    /** Suppression physique des DALR correspondant au DAL $dal */
    private function deleteDALR(DemandeApproL $dal): void
    {
        $dalrs = $this->em->getRepository(DemandeApproLR::class)->findBy(['numeroLigne' => $dal->getNumeroLigne(), 'numeroDemandeAppro' => $dal->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            $this->em->remove($dalr);
        }
    }
}
