<?php

namespace App\Service\migration;

use App\Service\FusionPdf;
use App\Model\dit\DitModel;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Traits\FormatageTrait;
use App\Service\genererPdf\GenererPdfDit;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrationPdfDitService
{
    use FormatageTrait;

    private DitRepository $ditRepository;
    private DitModel $ditModel;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->ditRepository =  $entityManager->getRepository(DemandeIntervention::class);
        $this->ditModel = new DitModel();
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

                // Créer l'objet de génération du PDF
                $ditPdf = new GenererPdfDit();

                // Récupérer les données nécessaires
                $historiqueMateriel = $this->historiqueInterventionMateriel($dit);

                // Génération du PDF et sauvegarde sur disque
                $ditPdf->genererPdfDit($dit, $historiqueMateriel);
                // Supposons que le PDF est sauvegardé sur disque ici

                // Fusion du PDF et migration (vérifier que cette méthode utilise bien des fichiers temporaires)
                // $this->fusionPdfmigrations($dit);

                // Envoi vers DWXCUWARE via streaming ou lecture par morceaux
                // $ditPdf->copyInterneToDOCUWARE(
                //     $dit->getNumeroDemandeIntervention(),
                //     str_replace("-", "", $dit->getAgenceServiceEmetteur())
                // );


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
        $fusionPdf = new FusionPdf();

        $mainPdf = 'C:/wamp64/www/Upload/dit/' . $dit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $dit->getAgenceServiceEmetteur()) . '.pdf';
        $files = [$mainPdf];
        $extension01 = '.' . pathinfo($dit->getPieceJoint01(), PATHINFO_EXTENSION);
        $extension02 = '.' . pathinfo($dit->getPieceJoint02(), PATHINFO_EXTENSION);
        $extension03 = '.' . pathinfo($dit->getPieceJoint03(), PATHINFO_EXTENSION);
        if (!empty($dit->getPieceJoint01()) && $extension01 === '.pdf') {
            $files[] = 'C:/wamp64/www/Hffintranet_DEV/migrations/DIT PJ/' . $dit->getPieceJoint01();
        }
        if (!empty($dit->getPieceJoint02() && $extension02 === '.pdf')) {
            $files[] = 'C:/wamp64/www/Hffintranet_DEV/migrations/DIT PJ/' . $dit->getPieceJoint02();
        }
        if (!empty($dit->getPieceJoint03()) && $extension03 === '.pdf') {
            $files[] = 'C:/wamp64/www/Hffintranet_DEV/migrations/DIT PJ/' . $dit->getPieceJoint03();
        }
        $outputFile = 'C:/wamp64/www/Upload/dit/' . $dit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $dit->getAgenceServiceEmetteur()) . '.pdf';
        $fusionPdf->mergePdfs($files, $outputFile);
    }

    private function recupDonnerDit(): array
    {
        $dits = $this->ditRepository->findDitMigration();


        foreach ($dits as $dit) {
            if (!empty($dit->getIdMateriel())) {
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
