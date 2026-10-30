<?php

namespace App\Service\da;

use App\Model\da\DaModel;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;

/**
 * Lignes de proposition (DAL / DALR) : choix de référence, enregistrement des propositions
 * et statut des lignes. Écrit uniquement dans DemandeApproL et DemandeApproLR.
 */
class DaPropositionLigneService
{
    private const EDIT = 0;

    private EntityManagerInterface $em;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private FileUploaderForDAService $daFileUploader;
    private DaModel $daModel;
    private DaFournisseurService $daFournisseurService;

    public function __construct(
        EntityManagerInterface $em,
        FileUploaderForDAService $daFileUploader,
        DaModel $daModel,
        DaFournisseurService $daFournisseurService
    ) {
        $this->em                       = $em;
        $this->daFileUploader           = $daFileUploader;
        $this->daModel                  = $daModel;
        $this->daFournisseurService     = $daFournisseurService;
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * Transforme la chaîne des références choisies ("ligne-tableau,ligne-tableau") en tableau de [numeroLigne, numLigneTableau]
     */
    public function parserRefs(?string $refsString): array
    {
        $selectedRefs = $refsString ? explode(',', $refsString) : [];

        $refs = [];
        foreach ($selectedRefs as $value) {
            $refs[] = explode('-', $value);
        }
        return $refs;
    }

    /**
     * Modifie l'ancien choix de DAL en ligne de DALR sélectionnée
     */
    public function modifierChoixEtLigneDal(array $refs, $dals): void
    {
        if (empty($refs)) return;

        $numDa = $dals[0]->getNumeroDemandeAppro();

        // reset les lignes de la page courante
        foreach ($refs as $ref) {
            foreach ($this->demandeApproLRRepository->findBy(['numeroLigne' => $ref[0], 'numeroDemandeAppro' => $numDa]) as $dalr) {
                $dalr->setChoix(false);
                $this->em->persist($dalr);
            }
        }
        $this->em->flush();

        // modifier la colonne choix
        foreach ($refs as $ref) {
            foreach ($this->demandeApproLRRepository->findBy(['numeroLigne' => $ref[0], 'numLigneTableau' => $ref[1], 'numeroDemandeAppro' => $numDa]) as $dalr) {
                $dalr->setChoix(true);
                $this->em->persist($dalr);
            }
        }
        $this->em->flush();

        // modification de la table demande_appro_L pour les lignes refs
        $this->modificationTableDaL($refs, $numDa);
    }

    /**
     * Applique le statut aux lignes de la dernière version de la DA (et remet edit à 0)
     */
    public function modifierStatutLignes(string $numDa, string $statut): void
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($dals as $dal) {
            $dal->setStatutDal($statut);
            $dal->setEdit(self::EDIT);
            $this->em->persist($dal);
        }

        $this->em->flush();
    }

    /**
     * Enregistre les DALR proposées pour les DAL
     *
     * @param bool $avecFamilleEtFournisseur true pour la DA avec DIT (libellés famille / sous-famille et numéro fournisseur), false pour la DA directe
     */
    public function enregistrerLignes($dals, $dalrList, string $statut, bool $avecFamilleEtFournisseur): void
    {
        foreach ($dalrList as $demandeApproLR) {
            $DAL = $dals->filter(function ($dal) use ($demandeApproLR) {
                return $dal->getNumeroLigne() === $demandeApproLR->getNumeroLigne();
            })->first();

            $this->em->persist($this->ajoutDonnerDaLR($DAL, $demandeApproLR, $statut, $avecFamilleEtFournisseur));
        }
        $this->em->flush();
    }

    private function modificationTableDaL(array $refs, string $numDa): void
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        for ($i = 0; $i < count($refs); $i++) {
            $dals = $this->demandeApproLRepository->findBy(['numeroLigne' => $refs[$i][0], 'numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigne' => $refs[$i][0], 'numLigneTableau' => $refs[$i][1], 'numeroDemandeAppro' => $numDa], ['numeroLigne' => 'ASC']);

            if (isset($dalrs[0]) && isset($dals[0])) {
                $dals[0]
                    ->setEstValidee($dalrs[0]->getEstValidee())
                    ->setEstModifier($dalrs[0]->getChoix())
                ;

                $this->em->persist($dals[0]);
            }
        }

        $this->em->flush();
    }

    private function ajoutDonnerDaLR(DemandeApproL $DAL, DemandeApproLR $demandeApproLR, string $statut, bool $avecFamilleEtFournisseur): DemandeApproLR
    {
        $numeroDemandeAppro = $DAL->getNumeroDemandeAppro();
        /** @var ?DemandeApproLR $demandeApproLR_Ancien */
        $demandeApproLR_Ancien = $this->demandeApproLRRepository->getDalrByPageAndRow($numeroDemandeAppro, $demandeApproLR->getNumeroLigne(), $demandeApproLR->getNumLigneTableau());

        $file = $demandeApproLR->getNomFicheTechnique(); // fiche technique de la DALR
        $fileNames = $demandeApproLR->getFileNames(); // pièces jointes de la DALR

        if ($demandeApproLR_Ancien) {
            $ficheTechnique = $this->daFileUploader->uploadDaFile($file, $numeroDemandeAppro, FileUploaderForDAService::FILE_TYPE["FICHE_TECHNIQUE"]);
            $fileNames = $this->daFileUploader->uploadMultipleDaFiles($fileNames, $numeroDemandeAppro, FileUploaderForDAService::FILE_TYPE["DEVIS"]);
            $demandeApproLR_Ancien
                ->setNomFicheTechnique($ficheTechnique)
                ->setFileNames($fileNames);

            $DAL->getDemandeApproLR()->add($demandeApproLR_Ancien);

            return $demandeApproLR_Ancien;
        }

        $demandeApproLR
            ->setDemandeApproL($DAL)
            ->setNumeroDemandeAppro($numeroDemandeAppro)
            ->setQteDem($DAL->getQteDem())
            ->setArtConstp($DAL->getArtConstp())
        ;

        if ($avecFamilleEtFournisseur) {
            $libelleFamille = $this->daModel->getLibelleFamille($demandeApproLR->getArtFams1()); // changement de code famille en libelle famille
            $libelleSousFamille = $this->daModel->getLibelleSousFamille($demandeApproLR->getArtFams2(), $demandeApproLR->getArtFams1()); // changement de code sous famille en libelle sous famille

            $demandeApproLR
                ->setCodeFams1($demandeApproLR->getArtFams1() == '' ? NULL : $demandeApproLR->getArtFams1()) // ceci doit toujour avant le setArtFams1
                ->setCodeFams2($demandeApproLR->getArtFams2() == '' ? NULL : $demandeApproLR->getArtFams2()) // ceci doit toujour avant le setArtFams2
                ->setArtFams1($libelleFamille == '' ? NULL : $libelleFamille) // ceci doit toujour après le codeFams1
                ->setArtFams2($libelleSousFamille == '' ? NULL : $libelleSousFamille) // ceci doit toujour après le codeFams2
            ;
        }

        $demandeApproLR
            ->setDateFinSouhaite($DAL->getDateFinSouhaite())
            ->setStatutDal($statut)
            ->setNumeroDemandeDit($DAL->getNumeroDit())
            ->setJoursDispo($DAL->getJoursDispo())
        ;

        if ($avecFamilleEtFournisseur && $demandeApproLR->getNumeroFournisseur() == 0) {
            $demandeApproLR->setNumeroFournisseur($this->daFournisseurService->getNumeroFournisseur($demandeApproLR->getNomFournisseur())); // définir le numéro du fournisseur
        }

        if ($file) {
            $ficheTechnique = $this->daFileUploader->uploadDaFile($file, $numeroDemandeAppro, FileUploaderForDAService::FILE_TYPE["FICHE_TECHNIQUE"]);
            $demandeApproLR->setNomFicheTechnique($ficheTechnique);
        }

        if ($fileNames) {
            $fileNames = $this->daFileUploader->uploadMultipleDaFiles($fileNames, $numeroDemandeAppro, FileUploaderForDAService::FILE_TYPE["DEVIS"]);
            $demandeApproLR->setFileNames($fileNames);
        }

        $DAL->getDemandeApproLR()->add($demandeApproLR);

        return $demandeApproLR;
    }
}
