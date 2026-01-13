<?php

namespace App\Controller\Traits\da\validation;

use DateTime;
use Exception;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DaSoumisAValidation;
use App\Service\autres\VersionService;
use App\Service\fichier\TraitementDeFichier;
use App\Repository\da\DaObservationRepository;
use App\Service\genererPdf\da\GenererPdfDaDirect;

trait DaValidationDirectTrait
{
    use DaValidationTrait;
    private GenererPdfDaDirect $genererPdfDaDirect;
    private TraitementDeFichier $traitementDeFichier;
    private DaObservationRepository $daObservationRepository;
    private string $cheminDeBase;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationDirectTrait(): void
    {
        $this->initDaTrait();
        $em = $this->getEntityManager();
        $this->genererPdfDaDirect = new GenererPdfDaDirect();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
    }
    //==================================================================================================

    /** 
     * Création du fichier Excel et PDF pour une DA directe
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @return array
     */
    private function exporterDaDirectEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf(
            $numDa,
            $numeroVersion,
            function ($numDa) {
                $this->creationPDFDirect($numDa); // Création du PDF
            }
        );
    }

    /** 
     * Création du PDF pour une DA directe
     * 
     * @param string $numDa
     * @return void
     */
    private function creationPDFDirect(string $numDa): void
    {
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $observations = $this->daObservationRepository->findBy(['numDa' => $numDa], ['dateCreation' => 'ASC']);
        $this->genererPdfDaDirect->genererPdfBonAchatValide($da, $observations);
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
        $numeroVersionMax = $this->getEntityManager()->getRepository(DaSoumisAValidation::class)->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion($numeroVersion)
            ->setStatut(DemandeAppro::STATUT_DW_A_VALIDE)
            ->setDateSoumission(new DateTime())
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->getEntityManager()->persist($daSoumisAValidation);
        $this->getEntityManager()->flush();
    }

    /** 
     * Fonction pour mettre la DA à valider dans DW
     * 
     * @param string $numDa le numero de la demande appro pour laquelle on génère le PDF
     */
    private function fusionAndCopyToDW(string $numDa)
    {
        $allDevisPj = $this->getDevisPjPathPDFDW($numDa);
        $bav = $this->cheminDeBase . "$numDa/$numDa.pdf";
        $fichiersConvertis = $this->ConvertirLesPdf($allDevisPj);
        array_unshift($fichiersConvertis, $bav);
        $nomAvecCheminPdfFusionner = $this->cheminDeBase . "$numDa/$numDa#_a_valider.pdf";
        $this->traitementDeFichier->fusionFichers($fichiersConvertis, $nomAvecCheminPdfFusionner);
        $this->genererPdfDaDirect->copyToDWDaAValiderDirect($numDa);
    }

    /** 
     * Obtenir l'url des devis et pièces jointes
     */
    private function getDevisPjPathPDFDW(string $numDa)
    {
        $pjDals = $this->demandeApproLRepository->findAttachmentsByNumeroDA($numDa);
        $pjDalrs = $this->demandeApproLRRepository->findAttachmentsByNumeroDA($numDa);

        /** 
         * Fusionner les résultats des deux tables
         * @var array<int, array{numeroDemandeAppro: string, fileNames: array}>
         **/
        $allRows = array_merge($pjDals, $pjDalrs);
        $filePaths = [];

        foreach ($allRows as $row) {
            foreach ($row['fileNames'] ?? [] as $fileName) {
                $filePaths[] = "{$_ENV['BASE_PATH_FICHIER']}/da/$numDa/$fileName";
            }
        }
        return $filePaths;
    }

    private function ConvertirLesPdf(array $tousLesFichersAvecChemin)
    {
        $tousLesFichiers = [];
        foreach ($tousLesFichersAvecChemin as $filePath) {
            $tousLesFichiers[] = $this->convertPdfWithGhostscript($filePath);
        }

        return $tousLesFichiers;
    }

    private function convertPdfWithGhostscript($filePath)
    {
        $gsPath = 'C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe'; // Modifier selon l'OS
        $tempFile = $filePath . "_temp.pdf";

        // Vérifier si le fichier existe et est accessible
        if (!file_exists($filePath)) {
            throw new Exception("Fichier introuvable : $filePath");
        }

        if (!is_readable($filePath)) {
            throw new Exception("Le fichier PDF ne peut pas être lu : $filePath");
        }

        // Commande Ghostscript
        $command = "\"$gsPath\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$tempFile\" \"$filePath\"";
        // echo "Commande exécutée : $command<br>";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Sortie Ghostscript : " . implode("\n", $output);
            throw new Exception("Erreur lors de la conversion du PDF avec Ghostscript");
        }

        // Remplacement du fichier
        if (!rename($tempFile, $filePath)) {
            throw new Exception("Impossible de remplacer l'ancien fichier PDF.");
        }

        return $filePath;
    }
}
