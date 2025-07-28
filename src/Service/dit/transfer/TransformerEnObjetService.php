<?php

namespace App\Service\dit\transfer;

use App\Entity\dit\BcSoumis;
use App\Entity\dit\DitDevisSoumisAValidation;
use App\Entity\dit\DitOrsSoumisAValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class TransformerEnObjetService
{
    private DitOrsSoumisAValidation $ditOrsSoumis;

    private DitDevisSoumisAValidation $ditDevisSoumis;

    private BcSoumis $bcSoumis;

    private RecupDataAncienDitService $RecupDataAncienDitService;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->ditOrsSoumis = new DitOrsSoumisAValidation();
        $this->ditDevisSoumis = new DitDevisSoumisAValidation();
        $this->bcSoumis = new BcSoumis();
        $this->RecupDataAncienDitService = new RecupDataAncienDitService($entityManagerInterface);
    }

    /**
     * cette Methode permet de transformer un tableau en Objet
     *
     * @param array $ancienDits
     * @return array tableau d'ojet demande d'intervention
     */
    public function transformDitEnObjet(array $ancienDits, ProgressBar $progressBar): array
    {
        $ditAnciens = [];
        foreach ($ancienDits as $ancienDit) {
            // $ditAnciens[] = $this->ditEnObjet($ancienDit);
            $ditAnciens[] = $this->RecupDataAncienDitService->ditEnObjet($ancienDit);

            // Faire avancer la barre de progression
            $progressBar->advance();
        }

        return $ditAnciens;
    }

    public function transformDevisEnObjet(array $ancienDevis, ProgressBar $progressBar): array
    {
        $devisAnciens = [];
        foreach ($ancienDevis as $ancienDevi) {
            $devisAnciens[] = $this->devisEnObjet($ancienDevi);

            // Faire avancer la barre de progression
            $progressBar->advance();
        }

        return $devisAnciens;
    }

    public function devisEnObjet(array $dev): DitDevisSoumisAValidation
    {
        return $this->ditDevisSoumis
            ->setNumeroDit($dev['NumeroDit'])
            ->setNumeroDevis($dev['NumeroDevis'])
            ->setNumeroItv($dev['NumeroItv'])
            ->setNombreLigneItv($dev['NombreLigneItv'])
            ->setMontantItv($dev['MontantItv'])
            ->setNumeroVersion($dev['NumeroVersion'])
            ->setMontantPiece($dev['MontantPiece'])
            ->setMontantMo($dev['MontantMo'])
            ->setMontantAchatLocaux($dev['MontantAchatLocaux'])
            ->setMontantFraisDivers($dev['MontantFraisDivers'])
            ->setMontantLubrifiants($dev['MontantLubrifiants'])
            ->setLibellelItv($dev['LibellelItv'])
            ->setStatut($dev['Statut'])
            ->setDateHeureSoumission($dev['DateHeureSoumission'])
            ->setMontantForfait($dev['MontantForfait'])
            ->setNatureOperation($dev['NatureOperation'])
            ->setDevise($dev['Devise'])
            ->setDevisVenteOuForfait($dev['DevisVenteOuForfait'])
        ;
    }

    public function transformBcEnObjet(array $ancienBcs, ProgressBar $progressBar): array
    {
        $ancienBcs = [];
        foreach ($ancienBcs as $ancienBc) {
            $ancienBcs[] = $this->bcEnObjet($ancienBc);

            // Faire avancer la barre de progression
            $progressBar->advance();
        }

        return $ancienBcs;
    }

    public function bcEnObjet(array $bcs): BcSoumis
    {
        return $this->bcSoumis
            ->setNumDit($bcs['NumDit'])
            ->setNumDevis($bcs['NumDevis'])
            ->setNumBc($bcs['NumBc'])
            ->setNumVersion($bcs['NumVersion'])
            ->setDateBc($bcs['DateBc'])
            ->setDateDevis($bcs['DateDevis'])
            ->setMontantDevis($bcs['MontantDevis'])
            ->setDateHeureSoumission($bcs['DateHeureSoumission'])
            ->setNomFichier($bcs['NomFichier'])
        ;
    }

    public function transformOrEnObjet(array $ancienOrs, ProgressBar $progressBar): array
    {
        $OrAnciens = [];
        foreach ($ancienOrs as $ancienOr) {
            $OrAnciens[] = $this->orEnObjet($ancienOr);

            // Faire avancer la barre de progression
            $progressBar->advance();
        }

        return $OrAnciens;
    }

    public function OrEnObjet(array $ors): DitOrsSoumisAValidation
    {
        return $this->ditOrsSoumis
            ->setNumeroOR($ors['NumeroOR'])
            ->setNumeroItv($ors['NumeroItv'])
            ->setNombreLigneItv($ors['NombreLigneItv'])
            ->setMontantItv($ors['MontantItv'])
            ->setNumeroVersion($ors['NumeroVersion'])
            ->setMontantPiece($ors['MontantPiece'])
            ->setMontantMo($ors['MontantMo'])
            ->setMontantAchatLocaux($ors['MontantAchatLocaux'])
            ->setMontantFraisDivers($ors['MontantFraisDivers'])
            ->setMontantLubrifiants($ors['MontantLubrifiants'])
            ->setLibellelItv($ors['LibellelItv'])
            ->setDateSoumission($ors['DateSoumission'])
            ->setHeureSoumission($ors['HeureSoumission'])
            ->setStatut($ors['Statut'])
            ->setMigration($ors['Migration'])
        ;
    }
}
