<?php

namespace App\Service\migration;

use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DemandeIntervention;
use App\Model\dit\DitModel;
use App\Repository\dit\DitRepository;
use App\Service\FusionPdf;
use App\Service\genererPdf\GenererPdfDit;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrationPdfDitService
{
    use FormatageTrait;

    private DitRepository $ditRepository;

    private DitModel $ditModel;

    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->ditRepository = $entityManager->getRepository(DemandeIntervention::class);
        $this->ditModel = new DitModel();
        $this->logger = $logger;
    }

    public function migrationPdfDit($output)
    {
        // Augmenter temporairement la limite de mémoire
        ini_set('memory_limit', '1024M');

        $dits = $this->recupDonnerDit();

        $total = count($dits);
        $batchSize = 3; // Par exemple, 10 éléments par lot

        // Diviser les dits en lots
        $batches = array_chunk($dits, $batchSize);

        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        foreach ($batches as $batch) {
            foreach ($batch as $dit) {
                try {
                    // Créer l'objet de génération du PDF
                    $ditPdf = new GenererPdfDit();

                    // Récupérer les données nécessaires
                    $historiqueMateriel = $this->historiqueInterventionMateriel($dit);

                    // Génération du PDF et sauvegarde sur disque
                    $ditPdf->genererPdfDit($dit, $historiqueMateriel);

                    // Fusion du PDF et migration
                    $this->fusionPdfmigrations($dit);

                    // Envoi vers DWXCUWARE via streaming ou lecture par morceaux
                    $ditPdf->copyInterneToDOCUWARE(
                        $dit->getNumeroDemandeIntervention(),
                        str_replace("-", "", $dit->getAgenceServiceEmetteur())
                    );

                    $this->logger->info(sprintf('DIT %s traité avec succès.', $dit->getNumeroDemandeIntervention()));

                } catch (\Exception $e) {
                    $this->logger->error(sprintf('Erreur lors du traitement du DIT %s: %s', $dit->getNumeroDemandeIntervention(), $e->getMessage()));
                    $output->writeln(sprintf('<error>Erreur lors du traitement du DIT %s: %s</error>', $dit->getNumeroDemandeIntervention(), $e->getMessage()));
                }

                // Avancer la barre de progression
                $progressBar->advance();

                // Libérer la mémoire de l'objet PDF
                // unset($ditPdf);
            }
            // Forcer la collecte des cycles de garbage collection après chaque lot
            gc_collect_cycles();
        }

        $output->writeln("\nNombre de résultats : " . $total);
        $progressBar->finish();
        $output->writeln("\nTerminé !");
    }

    private function fusionPdfmigrations($dit)
    {
        try {
            $fusionPdf = new FusionPdf();
            $uploadDir = 'C:/wamp64/www/Upload/dit/';
            $migrationDir = 'C:/wamp64/www/Hffintranet_DEV/migrations/DIT PJ/';

            $mainPdf = $uploadDir . $dit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $dit->getAgenceServiceEmetteur()) . '.pdf';
            $files = [$mainPdf];
            $processedPjs = [];

            for ($i = 1; $i <= 3; $i++) {
                $pieceJointe = $dit->{'getPieceJoint0' . $i}();
                if (! empty($pieceJointe) && ! in_array($pieceJointe, $processedPjs)) {
                    $extension = '.' . pathinfo($pieceJointe, PATHINFO_EXTENSION);
                    if ($extension === '.pdf') {
                        $filePath = $migrationDir . $pieceJointe;
                        if (file_exists($filePath)) {
                            $files[] = $filePath;
                            $processedPjs[] = $pieceJointe;
                        } else {
                            $this->logger->warning(sprintf('Fichier de pièce jointe manquant pour DIT %s: %s', $dit->getNumeroDemandeIntervention(), $filePath));
                        }
                    }
                }
            }

            $outputFile = $uploadDir . $dit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $dit->getAgenceServiceEmetteur()) . '.pdf';
            $fusionPdf->mergePdfs($files, $outputFile);
            $this->logger->info(sprintf('PDF fusionné avec succès pour DIT %s.', $dit->getNumeroDemandeIntervention()));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Erreur lors de la fusion des PDF pour DIT %s: %s', $dit->getNumeroDemandeIntervention(), $e->getMessage()));

            throw $e; // Re-throw the exception after logging
        }
    }

    private function recupDonnerDit(): array
    {
        $dits = $this->ditRepository->findDitMigration();


        foreach ($dits as $dit) {
            if (! empty($dit->getIdMateriel())) {
                $data = $this->ditModel->findAll($dit->getIdMateriel());
                if (empty($data)) {
                    echo "Aucune donnée trouvée pour le matériel ayant pour id : " . $dit->getIdMateriel();
                } else {
                    //Caractéristiques du matériel
                    $dit->setNumParc($data[0]['num_parc']);
                    $dit->setNumSerie($data[0]['num_serie']);
                    $dit->setIdMateriel($data[0]['num_matricule']);
                    $dit->setConstructeur($data[0]['constructeur']);
                    $dit->setModele($data[0]['modele']);
                    $dit->setDesignation($data[0]['designation']);
                    $dit->setCasier($data[0]['casier_emetteur']);
                    $dit->setLivraisonPartiel($dit->getLivraisonPartiel());
                    //Bilan financière
                    $dit->setCoutAcquisition($data[0]['prix_achat']);
                    $dit->setAmortissement($data[0]['amortissement']);
                    $dit->setChiffreAffaire($data[0]['chiffreaffaires']);
                    $dit->setChargeEntretient($data[0]['chargeentretien']);
                    $dit->setChargeLocative($data[0]['chargelocative']);
                    //Etat machine
                    $dit->setKm($data[0]['km']);
                    $dit->setHeure($data[0]['heure']);
                }

            }
        }

        return $dits;
    }

    private function historiqueInterventionMateriel($dits): array
    {
        $historiqueMateriel = $this->ditModel->historiqueMateriel($dits->getIdMateriel());
        foreach ($historiqueMateriel as $keys => $values) {
            foreach ($values as $key => $value) {
                if ($key == "datedebut") {
                    $historiqueMateriel[$keys]['datedebut'] = implode('/', array_reverse(explode("-", $value)));
                } elseif ($key === 'somme') {
                    $historiqueMateriel[$keys][$key] = explode(',', $this->formatNumber($value))[0];
                }
            }
        }

        return $historiqueMateriel;
    }
}
