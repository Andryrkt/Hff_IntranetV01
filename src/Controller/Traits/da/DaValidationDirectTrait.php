<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DaValider;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaValiderRepository;
use App\Service\autres\VersionService;

trait DaValidationDirectTrait
{
    use DaTrait;
    use DaValidationTrait;

    //=====================================================================================
    private DaValiderRepository $daValiderRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->daValiderRepository = $em->getRepository(DaValider::class);
    }
    //=====================================================================================

    /** 
     * Création du fichier Excel pour une DA directe
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @return array
     */
    private function creationFichierExcelDirect(string $numDa, int $numeroVersion): array
    {
        return $this->genererFichierExcelPourDa($numDa, $numeroVersion, function ($numDa, $donnees) {
            $this->enregistrerDaDirectDansDaValider($numDa, $donnees); // Enregistrement des données dans DaValider
        });
    }

    /** 
     * Enregistre les données de la DA directe dans DaValider
     * 
     * @param string $numDa
     * @param array $donnees
     * @return void
     */
    private function enregistrerDaDirectDansDaValider(string $numDa, array $donnees): void
    {
        $em = $this->getEntityManager();
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        foreach ($donnees as $donnee) {
            $daValider = new DaValider;

            $numeroVersion = $this->daValiderRepository->getNumeroVersionMax($numDa);
            $daValider
                ->setNumeroVersion(VersionService::autoIncrement($numeroVersion)) // numero de version de DaValider
            ;

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
}
