<?php

namespace App\Controller\Traits\da\validation;

use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\ExcelService;
use DateTime;

trait DaValidationTrait
{
    use DaTrait;

    /** 
     * Modification des tables DemandeAppro, DemandeApproL et DemandeApproLR
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @param array $prixUnitaire
     * @param array $refsValide
     * @return ?DemandeAppro
     */
    private function validerDemandeApproAvecLignes(string $numDa, int $numeroVersion, array $prixUnitaire = [], array $refsValide = []): ?DemandeAppro
    {
        $em = $this->getEntityManager();
        $user = $this->getUser();
        $nomutilisateur = $user->getNomUtilisateur();

        /** @var DemandeAppro|null $da */
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        if (!$da) return null;

        // 1. Mise à jour de la DA
        $da
            ->setEstValidee(true)
            ->setValidateur($user)
            ->setValidePar($nomutilisateur)
            ->setStatutDal(DemandeAppro::STATUT_VALIDE);
        $em->persist($da);

        // 2. Mise à jour des lignes DAL
        /** @var iterable<DemandeApproL> $dals les lignes de DAL dernière version */
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersion]);
        foreach ($dals as $dal) {
            $dal
                ->setEstValidee(true)
                ->setValidePar($nomutilisateur)
                ->setStatutDal(DemandeAppro::STATUT_VALIDE);

            if (isset($prixUnitaire[$dal->getNumeroLigne()])) {
                $dal->setPrixUnitaire($prixUnitaire[$dal->getNumeroLigne()]);
            }

            $em->persist($dal);
        }

        // 3. Mise à jour des lignes DALR
        /** @var iterable<DemandeApproLR> $dalrs les lignes de DALR correspondant au numéro de la DA $numDa */
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa]);
        foreach ($dalrs as $dalr) {
            $dalr
                ->setEstValidee(true)
                ->setValidePar($nomutilisateur)
                ->setStatutDal(DemandeAppro::STATUT_VALIDE);

            $this->mettreAJourChoixDalr($dalr, $refsValide);

            $em->persist($dalr);
        }

        return $da;
    }

    /** 
     * Mettre à jour le choix d'une ligne DALR
     * 
     * @param DemandeApproLR $dalr
     * @param array $refsValide
     * @return void
     */
    private function mettreAJourChoixDalr(DemandeApproLR $dalr, array $refsValide): void
    {
        if (empty($refsValide)) return;

        $dalr->setChoix(false);

        $numeroLigne = $dalr->getNumeroLigne();
        $numeroLigneTableau = $dalr->getNumLigneTableau();

        if (isset($refsValide[$numeroLigne]) && $numeroLigneTableau == $refsValide[$numeroLigne]) {
            $dalr->setChoix(true);
        }
    }

    /** 
     * Fonction pour générer un fichier Excel et PDF pour une DA
     * 
     * @param string   $numDa le numéro de la DA
     * @param int      $numeroVersion le numéro de version de la DA
     * @param callable $strategieEnregistrementPDF la stratégie d'enregistrement du PDF
     * @return array
     */
    private function exporterDaEnExcelEtPdf(string $numDa, int $numeroVersion, callable $strategieEnregistrementPDF): array
    {
        // 1. Récupération des lignes rectifiées de la DA
        $donnees = $this->getLignesRectifieesDA($numDa, $numeroVersion);

        // 2. Création du fichier PDF
        $strategieEnregistrementPDF($numDa);

        // 3. Transformation des entités en tableau pour Excel
        $donneesExcel = $this->convertirEntitesPourExcel($donnees);

        // 4. Génération du fichier Excel
        $fileName = $this->genererNomFichierExcel($numDa);
        $filePath = $this->genererCheminFichierExcel($numDa, $fileName);
        (new ExcelService())->createSpreadsheetEnregistrer($donneesExcel, $filePath);

        return [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'donnees'  => $donnees,
        ];
    }

    /** 
     * Convertit les entités en tableau pour Excel
     * 
     * @param array $entities
     * @return array
     */
    private function convertirEntitesPourExcel(array $entities): array
    {
        $tableau = [];
        $tableau[] = ['constructeur', 'reference', 'quantité', '', 'designation', 'PU'];

        foreach ($entities as $entity) {
            $tableau[] = [
                $entity->getArtConstp(),
                $entity->getArtRefp(),
                $entity->getQteDem(),
                '',
                $entity->getArtConstp() === 'ZDI' || $entity->getArtRefp() === 'ST' ? $entity->getArtDesi() : '',
                $entity->getArtConstp() === 'ZDI' || $entity->getArtRefp() === 'ST' ? $entity->getPrixUnitaire() : '',
            ];
        }

        return $tableau;
    }

    /** 
     * Génère le nom du fichier Excel
     * 
     * @param string $numDa
     * @return string
     */
    private function genererNomFichierExcel(string $numDa): string
    {
        return $numDa . '_' . (new DateTime())->format('Ymd_His') . '.xlsx';
    }

    /** 
     * Génère le chemin complet du fichier Excel
     * 
     * @param string $numDa
     * @param string $fileName
     * @return string
     */
    private function genererCheminFichierExcel(string $numDa, string $fileName): string
    {
        return $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa/$fileName";
    }
}
