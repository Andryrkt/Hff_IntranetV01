<?php

namespace App\Service\migration;

use App\Model\dit\DitModel;
use App\Repository\dit\DitRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DemandeIntervention;
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
        $dits = $this->recupDonnerDit();
        
        $total = count($dits);
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();
        //generation du pdf
        foreach ($dits as $dit) {
            $ditPdf = new GenererPdfDit();
            $historiqueMateriel = $this->historiqueInterventionMateriel($dit);
            $ditPdf->genererPdfDit($dit, $historiqueMateriel);
            //envoyer dans DWXCUWARE
            $ditPdf->copyInterneToDOXCUWARE($dit->getNumeroDemandeIntervention(), str_replace("-", "", $dit->getAgenceServiceEmetteur()));

            // Avancer la barre de progression d'une étape
            $progressBar->advance();
        }

        // Afficher le nombre de résultats
        $output->writeln("\nNombre de résultats : " . $total);
        $progressBar->finish();
        $output->writeln("\nTerminé !");
    }

    private function recupDonnerDit(): array
    {
        $dits = $this->ditRepository->findDitMigration();
        foreach ($dits as $dit) {
            if(!empty($dit->getIdMateriel())){
                $data = $this->ditModel->findAll($dit->getIdMateriel());
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