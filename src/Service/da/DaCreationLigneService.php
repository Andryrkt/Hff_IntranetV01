<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproParentLine;
use App\Model\da\DaModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Traite les lignes d'une DA à la création : suppression, uploads, champs calculés, persist.
 */
class DaCreationLigneService
{
    private EntityManagerInterface $em;
    private FileUploaderForDAService $daFileUploader;
    private DaService $daService;
    private DaModel $daModel;
    private DaFournisseurService $daFournisseurService;

    public function __construct(
        EntityManagerInterface $em,
        FileUploaderForDAService $daFileUploader,
        DaService $daService,
        DaModel $daModel,
        DaFournisseurService $daFournisseurService
    ) {
        $this->em = $em;
        $this->daFileUploader = $daFileUploader;
        $this->daService = $daService;
        $this->daModel = $daModel;
        $this->daFournisseurService = $daFournisseurService;
    }

    /** Lignes DAL d'une DA avec DIT (formulaire `DAL`). */
    public function traiterLignesAvecDit(FormInterface $formDAL, DemandeAppro $demandeAppro, string $numDa, string $statutDal): void
    {
        foreach ($formDAL as $subFormDAL) {
            /** @var DemandeApproL $demandeApproL */
            $demandeApproL = $subFormDAL->getData();

            if ($demandeApproL->getDeleted() == 1) {
                $this->em->remove($demandeApproL);
                continue;
            }

            $allFileNames = $this->gererFichiers($subFormDAL, $numDa);

            $demandeApproL
                ->setNumeroDemandeAppro($numDa)
                ->setStatutDal($statutDal)
                ->setPrixUnitaire($this->daModel->getPrixUnitaire($demandeApproL->getArtRefp())[0])
                ->setNumeroDit($demandeAppro->getNumeroDemandeDit())
                ->setJoursDispo($this->daService->getJoursRestants($demandeApproL))
                ->setFileNames($allFileNames)
            ;

            if ($demandeApproL->getNumeroFournisseur() == 0) {
                $demandeApproL->setNumeroFournisseur($this->daFournisseurService->getNumeroFournisseur($demandeApproL->getNomFournisseur())); // définir le numéro du fournisseur
            }

            $this->em->persist($demandeApproL);
        }
    }

    /** Lignes d'une DA parent / achat (formulaire `demandeApproParentLines`). */
    public function traiterLignesParent(FormInterface $formLines, string $numDa, string $statutDal): void
    {
        foreach ($formLines as $subFormDapL) {
            /** @var DemandeApproParentLine $demandeApproParentLine */
            $demandeApproParentLine = $subFormDapL->getData();

            if ($demandeApproParentLine->isDeleted()) {
                $this->em->remove($demandeApproParentLine);
                continue;
            }

            $allFileNames = $this->gererFichiers($subFormDapL, $numDa);

            $demandeApproParentLine
                ->setNumeroDemandeAppro($numDa)
                ->setStatutDal($statutDal)
                ->setJoursDispo($this->daService->getJoursRestants($demandeApproParentLine))
                ->setFileNames($allFileNames)
            ;

            $this->em->persist($demandeApproParentLine);
        }
    }

    /** Lignes d'un réappro mensuel : les lignes sans quantité ne sont pas persistées. */
    public function traiterLignesReappro(DemandeAppro $demandeAppro, string $numDa, string $statutDal): void
    {
        /** @var DemandeApproL $dal */
        foreach ($demandeAppro->getDAL() as $dal) {
            if ($dal->getQteDem()) {
                $dal
                    ->setNumeroDemandeAppro($numDa)
                    ->setDemandeAppro($demandeAppro)
                    ->setDateFinSouhaite($demandeAppro->getDateFinSouhaite())
                    ->setJoursDispo($this->daService->getJoursRestants($dal))
                    ->setStatutDal($statutDal);
                $this->em->persist($dal);
            } else {
                $demandeAppro->removeDAL($dal); // ne pas persister les DAL avec qteDem vide
                $this->em->remove($dal);
            }
        }
    }

    /** Supprime les fichiers retirés, uploade les nouveaux et renvoie la liste finale. */
    private function gererFichiers(FormInterface $subForm, string $numDa)
    {
        $filesToDelete = $subForm->get('filesToDelete')->getData();
        $existingFileNames = $subForm->get('existingFileNames')->getData();
        $newFiles = $subForm->get('fileNames')->getData();

        if ($filesToDelete) {
            $this->daFileUploader->deleteFiles(explode(',', $filesToDelete), $numDa);
        }

        return $this->daFileUploader->handleFileUpload($newFiles, $existingFileNames, $numDa, FileUploaderForDAService::FILE_TYPE["DEVIS"]);
    }
}
