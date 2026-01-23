<?php

namespace App\Service\migration\magasin;


use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Service\genererPdf\magasin\devis\PdfMigrationDevisMagasinVp;

class MigrationPdfDevisMagasinVdService
{
    use devisMagasinMigrationTrait;

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

        // $numeroDevisVd = $devisMagasinRepository->getNumeroDevisMigrationVd();
        $numeroDevisVd = [24208044, 24208045, 24208048, 24208075, 24208154, 34204642, 34204643, 34204644, 44212166, 44212167, 44212169, 44212172, 44212173, 44212187, 44212196, 44212203, 44212204, 44212206, 44212208, 44212209, 44212210, 44212212, 44212214, 44212219, 44212220, 44212223, 44212224, 44212226, 44212228, 44212229, 44212230, 44212231, 44212232, 44212235, 44212237, 44212238, 44212239, 44212245, 44212250, 44212253, 44212255, 44212256, 44212259, 44212260, 44212261, 44212268, 44212272, 44212287, 44212290, 44212291, 44212292, 44212300, 44212301, 44212305, 44212306, 44212308, 44212310, 44212312, 44212314, 44212316, 44212317, 44212320, 44212321, 44212325, 44212332, 44212334, 44212336, 44212337, 44212340, 44212350, 44212351, 44212353, 44212357, 44212358, 44212360, 44212361, 44212363, 44212364, 44212368, 44212369, 44212372, 44212374, 44212375, 44212376, 44212378, 44212379, 44212388, 44212391, 44212393, 44212394, 44212402, 44212404, 44212411, 44212412, 44212413, 44212416, 44212417, 44212419, 44212420, 44212421, 44212422, 44212423, 44212425, 44212426, 44212427, 44212428, 44212429, 44212430, 44212436, 44212439, 44212445, 44212447, 44212448, 44212449, 44212450, 44212456, 44212465, 44212466, 44212469, 44212473, 44212474, 44212482, 44212484, 44212485, 44212487, 44212490, 44212491, 44212492, 44212493, 44212494, 44212495, 44212496, 44212497, 44212498, 44212499, 44212500, 44212501, 44212508, 44212510, 44212511, 44212513, 44212514, 44212516, 44212517, 44212521, 44212523, 44212524, 44212530, 44212533, 44212534, 44212536, 44212539, 44212546, 44212550, 44212552, 44212553, 44212554, 44212557, 44212558, 44212559, 44212562, 44212564, 44212571, 44212573, 44212574, 44212579, 44212580, 44212581, 44212583, 44212593, 44212594, 44212595, 44212596, 44212600, 44212601, 44212606, 44212609, 44212622, 44212624, 44212625, 44212626, 44212627, 44212628, 44212631, 44212632, 44212634];

        //recupération des données à migrer
        $listeDevisMagasinModel = new ListeDevisMagasinModel();
        // [$devisVp, $devisVd, $numeroDevisVp, $numeroDevisVd] = $this->getDataCsv();

        $numerodevis = TableauEnStringService::simpleNumeric($numeroDevisVd);
        $devisMagasins = $listeDevisMagasinModel->getDevisMagasinToMigrationPdf($numerodevis);

        foreach ($devisMagasins as $numerodevis => &$devisMagasin) {
            // $devisMagasin['statut_temp'] = $devisMagasinRepository->getStatutTempVd($numerodevis);
            $devisMagasin['statut_temp'] = "Validé - à envoyer au client";
        }

        // foreach ($devisMagasins as $numerodevis => &$devisMagasin) {
        //     $devisMagasin['statut_verification_prix'] = $devisVp[$numerodevis]['statut_verification_prix'] ?? '';
        //     $devisMagasin['statut_validation_devis_agence'] = $devisVp[$numerodevis]['statut_validation_devis_agence'] ?? '';
        // }

        //compter le nombre total de devis à migrer
        $total = count($devisMagasins);
        $batchSize = 5; // Par exemple, 5 éléments par lot

        // Diviser les devis en lots
        $batches = array_chunk($devisMagasins, $batchSize);

        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        foreach ($batches as $batch) {
            foreach ($batch as $devis) {
                // créer l'objet de génération du PDF
                $pdfMigrationDevisMagasinVp = new PdfMigrationDevisMagasinVp();

                //génération du PDF et sauvegarde sur disque
                $numeroDevis = $devis['numero_devis'];
                $suffix = $listeDevisMagasinModel->constructeurPieceMagasinMigration($numeroDevis);

                $fileName = "validationdevis_$numeroDevis-1#$suffix!noreply.migration.pdf";;
                $path = "C:\wamp64\www\Upload\magasin\migrations\devis_vd/";
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $filePath = $path . $fileName;
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
