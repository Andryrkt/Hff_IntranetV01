<?php

namespace App\Service\migration\magasin;

use Dom\Entity;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Service\genererPdf\magasin\devis\PdfMigrationDevisMagasinVp;
use App\Service\TableauEnStringService;

class MigrationPdfDevisMagasinService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function migrationPdfDevisMagasin($output)
    {
        // Augmenter temporairement la limite de mémoire
        ini_set('memory_limit', '1024M');

        // repository devis magaisn
        $devisMagasinRepository = $this->entityManager->getRepository(DevisMagasin::class);

        //recupération des données à migrer
        $listeDevisMagasinModel = new ListeDevisMagasinModel();
        $numerodevis = TableauEnStringService::simpleNumeric([19399433, 19399434, 19399432, 44211557]);
        $devisMagasin = $listeDevisMagasinModel->getDevisMagasinToMigrationPdf($numerodevis);

        //compter le nombre total de devis à migrer
        $total = count($devisMagasin);
        $batchSize = 5; // Par exemple, 5 éléments par lot

        // Diviser les devis en lots
        $batches = array_chunk($devisMagasin, $batchSize);

        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        foreach ($batches as $batch) {
            foreach ($batch as $devis) {
                // créer l'objet de génération du PDF
                $pdfMigrationDevisMagasinVp = new PdfMigrationDevisMagasinVp();

                //génération du PDF et sauvegarde sur disque
                $numeroDevis = $devis['numero_devis'];
                $suffix = $listeDevisMagasinModel->constructeurPieceMagasinMigration($numeroDevis);

                $fileName = "negverificationprix_$numeroDevis-1#$suffix!noreply.migration.pdf";;
                $path = "C:\wamp64\www\Upload\magasin\migrations\devis/" . $numeroDevis;
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $filePath = $path . '/' . $fileName;
                $pdfMigrationDevisMagasinVp->genererPdf($devis, $filePath);

                // Avancer la barre de progression
                $progressBar->advance();
            }
            // Forcer la collecte des cycles de garbage collection après chaque lot
            gc_collect_cycles();
        }

        $output->writeln("\nNombre de résultats : " . $total);
        $progressBar->finish();
        $output->writeln("\nTerminé !");
    }
}
