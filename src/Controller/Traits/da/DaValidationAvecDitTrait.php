<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DaValider;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaValiderRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\dit\DitRepository;
use App\Service\autres\VersionService;
use App\Service\genererPdf\GenererPdfDaAvecDit;

trait DaValidationAvecDitTrait
{
    use DaTrait;
    use DaValidationTrait;

    //====================================================================================================
    private DitRepository $ditRepository;
    private DaValiderRepository $daValiderRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->daValiderRepository = $em->getRepository(DaValider::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
    }
    //====================================================================================================


    /** 
     * Création du fichier Excel et PDF pour une DA avec DIT
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @return array
     */
    private function exporterDaAvecDitEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf(
            $numDa,
            $numeroVersion,
            function ($numDa, $donnees) {
                $this->enregistrerDaAvecDitDansDaValider($numDa, $donnees); // Enregistrement des données dans DaValider
            },
            function ($numDa) {
                $this->creationPDFAvecDit($numDa); // Création du PDF
            }
        );
    }

    /** 
     * Enregistre les données de la DA avec DIT dans DaValider
     * 
     * @param string $numDa
     * @param array $donnees
     * @return void
     */
    private function enregistrerDaAvecDitDansDaValider(string $numDa, array $donnees): void
    {
        $em = $this->getEntityManager();
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        foreach ($donnees as $donnee) {
            $daValider = new DaValider;

            $numeroVersion = $this->daValiderRepository->getNumeroVersionMax($numDa);
            [$numOr,] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($da->getNumeroDemandeDit());
            $daValider
                ->setNumeroVersion(VersionService::autoIncrement($numeroVersion)) // numero de version de DaValider
                ->setStatutOr($numOr ? DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION : DitOrsSoumisAValidation::STATUT_VIDE)
                ->setOrResoumettre((bool) $numOr);

            $daValider->enregistrerDa($da); // enregistrement pour DA

            if ($donnee instanceof DemandeApproL) {
                $daValider->enregistrerDal($donnee); // enregistrement pour DAL
            } else if ($donnee instanceof DemandeApproLR) {
                $daValider->enregistrerDalr($donnee); // enregistrement pour DALR
            }

            $em->persist($daValider);
        }

        $em->flush();
    }

    /** 
     * Création du PDF pour une DA avec DIT
     * 
     * @param string $numDa
     * @return void
     */
    private function creationPDFAvecDit(string $numDa): void
    {
        $genererPdfDaAvecDit = new GenererPdfDaAvecDit;
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);
        $genererPdfDaAvecDit->genererPdf($dit, $da);
    }
}
