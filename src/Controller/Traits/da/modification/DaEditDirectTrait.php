<?php

namespace App\Controller\Traits\da\modification;

use DateTime;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumisAValidation;
use App\Service\autres\VersionService;
use App\Repository\da\DaObservationRepository;
use App\Service\genererPdf\GenererPdfDaDirect;
use App\Repository\da\DaSoumisAValidationRepository;

trait DaEditDirectTrait
{
    use DaEditTrait;

    //==================================================================================================
    protected DaObservationRepository $daObservationRepository;
    protected DaSoumisAValidationRepository $daSoumisAValidationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditDirectTrait(): void
    {
        $em = $this->getEntityManager();
        
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->daSoumisAValidationRepository = $em->getRepository(DaSoumisAValidation::class);
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

    public function statutDaModifier(DemandeAppro $demandeAppro): string
    {
        $statutDwAModifier = $demandeAppro->getStatutDal() === DemandeAppro::STATUT_DW_A_MODIFIER;
        return $statutDwAModifier ? DemandeAppro::STATUT_A_VALIDE_DW : DemandeAppro::STATUT_SOUMIS_APPRO;
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
     * Ajoute les données d'une Demande d'Achat direct dans la table `DaSoumisAValidation`
     *
     * @param DemandeAppro $demandeAppro  Objet de la demande d'achat direct à traiter
     */
    private function ajouterDansDaSoumisAValidation(DemandeAppro $demandeAppro): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion($numeroVersion)
            ->setStatut($demandeAppro->getStatutDal())
            ->setDateSoumission(new DateTime())
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->getEntityManager()->persist($daSoumisAValidation);
        $this->getEntityManager()->flush();
    }

    /** 
     * Fonction pour créer le PDF sans Dit à valider DW
     * 
     * @param DemandeAppro $demandeAppro la demande appro pour laquelle on génère le PDF
     */
    private function creationPdfSansDitAvaliderDW(DemandeAppro $demandeAppro)
    {
        $genererPdfDaDirect = new GenererPdfDaDirect;
        $dals = $demandeAppro->getDAL();

        $genererPdfDaDirect->genererPdfAValiderDW($demandeAppro, $dals, $this->getUserMail());
        $genererPdfDaDirect->copyToDWDaAValider($demandeAppro->getNumeroDemandeAppro());
    }
}
