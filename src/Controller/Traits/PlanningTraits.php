<?php

namespace App\Controller\Traits;

use App\Model\planning\PlanningModel;
use App\Entity\dit\DemandeIntervention;
use App\Entity\planning\PlanningSearch;
use App\Entity\planning\PlanningMateriel;
use App\Entity\dit\DitOrsSoumisAValidation;

trait PlanningTraits
{
    private function orEnString($criteria): string
    {
        $numOrValide = $this->transformEnSeulTableau($criteria);

        return implode("','", $numOrValide);
    }

    private function recupNumOrValider($criteria)
    {
        $PlanningModel  = new PlanningModel();
        /** @var array $numeroOrsItv @var array $numeroOr */
        [$numeroOrsItv, $numeroOr] = $PlanningModel->recuperationNumOrValider($criteria);

        $resNumor = $this->orEnString($numeroOrsItv);
        $orSansItv = $this->orEnString($numeroOr);

        return [
            'orAvecItv' => $resNumor,
            'orSansItv' => $orSansItv
        ];
    }

    private function recupNumOrBackValider($criteria)
    {
        $PlanningModel  = new PlanningModel();
        $numeroOrs = $PlanningModel->recuperationNumOrValider($criteria);
        return $numeroOrs;
    }


    private function recupNumORItvValide($numeroOrs, $em)
    {
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            // dump($numeroOr);
            // dump($numItv);
            if (!empty($numItv)) {
                foreach ($numItv as  $value) {
                    $numOrValide[] = $numeroOr['numero_or'] . '-' . $value;
                }
            }
        }
        return $numOrValide;
    }
    /*
    private function numeroOrValide($numeroOrs, $PlanningModel, $em)
    {
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if(!empty($numItv)){
                $numItvs = $PlanningModel->recupNumeroItv($numeroOr['numero_or'],$this->orEnString($numItv));
                if($numItvs[0]['nbitv'] === "0"){
                    $numOrValide[] = $numeroOr;
                }
            }
        }

        return $numOrValide;
    }
    */


    /**======================
     * Excel
     ========================*/
    private function creationObjetCriteria(array $criteria): PlanningSearch
    {
        //crée une objet à partir du tableau critère reçu par la session
        $this->planningSearch
            ->setAgence($criteria["agence"])
            ->setAnnee($criteria["annee"])
            ->setInterneExterne($criteria["interneExterne"])
            ->setFacture($criteria["facture"])
            ->setPlan($criteria["plan"])
            ->setDateDebut($criteria["dateDebut"])
            ->setDateFin($criteria["dateFin"])
            ->setNumOr($criteria["numOr"])
            ->setNumSerie($criteria["numSerie"])
            ->setIdMat($criteria["idMat"])
            ->setNumParc($criteria["numParc"])
            ->setAgenceDebite($criteria["agenceDebite"])
            ->setServiceDebite($criteria["serviceDebite"])
            ->setTypeligne($criteria["typeligne"])

        ;

        return $this->planningSearch;
    }

    private function creationTableauObjetExport(array $data): array
    {

        $objetPlanning = [];
        //Recuperation de idmat et les truc
        foreach ($data as $item) {
            $planningMateriel = new PlanningMateriel();

            //initialisation
            $planningMateriel
                ->setCodeSuc($item['codesuc'])
                ->setLibSuc($item['libsuc'])
                ->setCodeServ($item['codeserv'])
                ->setLibServ($item['libserv'])
                ->setIdMat($item['idmat'])
                ->setMarqueMat($item['markmat'])
                ->setTypeMat($item['typemat'])
                ->setNumSerie($item['numserie'])
                ->setNumParc($item['numparc'])
                ->setCasier($item['casier'])
                ->setAnnee($item['annee'])
                ->setMois($item['mois'])
                ->setPos($item['slor_pos'])
                ->setOrIntv($item['orintv'])
                ->setCommentaire($item['commentaire'])
                ->setPlan($item['plan'])

            ;
            $objetPlanning[] = $planningMateriel;
        }

        return $objetPlanning;
    }

    private function creationTableauObjetPlanning(array $data, array $back, $em): array
    {
        $objetPlanning = [];
        //Recuperation de idmat et les truc
        foreach ($data as $item) {
            $planningMateriel = new PlanningMateriel();
            // dump($item['orintv']);
            $ditRepositoryConditionner = $em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $item['orintv'])[0]]);

            if ($ditRepositoryConditionner !== null) {
                $numDit = $ditRepositoryConditionner->getNumeroDemandeIntervention();
                $migration = $ditRepositoryConditionner->getMigration();
            } else {
                // Handle the case where the result is not found, e.g., log a message or set default values
                $numDit = null;  // or some other default value
                $migration = null;  // or some other default value
            }



            if (in_array($item['orintv'], $back)) {
                $backOrder = 'Okey';
            } else {
                $backOrder = '';
            }

            //initialisation
            $planningMateriel
                ->setCodeSuc($item['codesuc'])
                ->setLibSuc($item['libsuc'])
                ->setCodeServ($item['codeserv'])
                ->setLibServ($item['libserv'])
                ->setIdMat($item['idmat'])
                ->setMarqueMat($item['markmat'])
                ->setTypeMat($item['typemat'])
                ->setNumSerie($item['numserie'])
                ->setNumParc($item['numparc'])
                ->setCasier($item['casier'])
                ->setAnnee($item['annee'])
                ->setMois($item['mois'])
                ->setOrIntv($item['orintv'])
                ->setQteCdm($item['qtecdm'])
                ->setQteLiv($item['qtliv'])
                ->setQteAll($item['qteall'])
                ->setNumDit($numDit)
                // ->setNumeroOr($item['numeroor'])
                ->addMoisDetail($item['mois'], $item['annee'], $item['orintv'], $item['qtecdm'], $item['qtliv'], $item['qteall'], $numDit, $migration, $item['commentaire'], $backOrder)
            ;
            $objetPlanning[] = $planningMateriel;
        }
        return $objetPlanning;
    }
    private function creationTableauObjetPlanningMagasin(array $data, array $back): array
    {
        $objetPlanning = [];
        //Recuperation de idmat et les truc
        foreach ($data as $item) {
            $planningMateriel = new PlanningMateriel();
            if (in_array($item['orintv'], array_column($back, 'intervention'))) {
                $backOrder = 'Okey';
            } else {
                $backOrder = '';
            }
            //initialisation
            $planningMateriel
                ->setCodeSuc($item['codesuc'])
                ->setLibSuc($item['libsuc'])
                ->setCodeServ($item['codeserv'])
                ->setLibServ($item['libserv'])
                ->setIdMat($item['idmat'])
                ->setMarqueMat($item['markmat'])
                ->setTypeMat($item['typemat'])
                ->setNumSerie($item['numserie'])
                ->setNumParc($item['numparc'])
                ->setCasier($item['casier'])
                ->setAnnee($item['annee'])
                ->setMois($item['mois'])
                ->setOrIntv($item['orintv'])
                ->setQteCdm($item['qtecdm'])
                ->setQteLiv($item['qtliv'])
                ->setQteAll($item['qteall'])
                ->setBack($backOrder)
                // ->setNumeroOr($item['numeroor'])
                ->addMoisDetailMagasin($item['mois'], $item['annee'], $item['orintv'], $item['qtecdm'], $item['qtliv'], $item['qteall'], $item['commentaire'], $backOrder)
            ;
            $objetPlanning[] = $planningMateriel;
        }
        return $objetPlanning;
    }
    private function ajoutMoiDetail(array $objetPlanning): array
    {
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = [];
        foreach ($objetPlanning as $materiel) {
            $key = $materiel->getIdMat(); // Utiliser idMat comme clé unique
            if (!isset($fusionResult[$key])) {
                $fusionResult[$key] = $materiel; // Si la clé n'existe pas, on l'ajoute
            } else {
                // Si l'élément existe déjà, on fusionne les détails des mois
                foreach ($materiel->moisDetails as $moisDetail) {

                    $fusionResult[$key]->addMoisDetail(
                        $moisDetail['mois'],
                        $moisDetail['annee'],
                        $moisDetail['orIntv'],
                        $moisDetail['qteCdm'],
                        $moisDetail['qteLiv'],
                        $moisDetail['qteAll'],
                        $moisDetail['numDit'],
                        $moisDetail['migration'],
                        $moisDetail['commentaire'],
                        $moisDetail['back']
                    );
                }
            }
        }
        return $fusionResult;
    }
    private function ajoutMoiDetailMagasin(array $objetPlanning): array
    {
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = [];
        foreach ($objetPlanning as $materiel) {
            $key = $materiel->getIdMat(); // Utiliser idMat comme clé unique
            if (!isset($fusionResult[$key])) {
                $fusionResult[$key] = $materiel; // Si la clé n'existe pas, on l'ajoute
            } else {
                // Si l'élément existe déjà, on fusionne les détails des mois
                foreach ($materiel->moisDetails as $moisDetail) {

                    $fusionResult[$key]->addMoisDetailMagasin(
                        $moisDetail['mois'],
                        $moisDetail['annee'],
                        $moisDetail['orIntv'],
                        $moisDetail['qteCdm'],
                        $moisDetail['qteLiv'],
                        $moisDetail['qteAll'],
                        $moisDetail['commentaire'],
                        $moisDetail['back']
                    );
                }
            }
        }
        return $fusionResult;
    }
    /**
     * fonction pour affichage des 12 mois glissantes (3 mois suivant, 6 mois suivant, Année encours, Année suivant)
     *
     * @param array $data
     * @param integer $selectedOption
     * @return array
     */
    private function prepareDataForDisplay(array $data, int $selectedOption): array
    {
        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $currentMonth = (int)date('n') - 1; // Index du mois actuel (0-11)
        $currentYear = (int)date('Y');

        $selectedMonths = $this->getSelectedMonths($months, $currentMonth, $currentYear, $selectedOption);

        $preparedData = array_map(function ($item) use ($months, $selectedMonths) {
            $moisDetails = property_exists($item, 'moisDetails') && is_array($item->getMoisDetails())
                ? $item->getMoisDetails()
                : [];

            $filteredMonths = array_filter(array_map(function ($detail) use ($months, $selectedMonths) {
                if (!is_array($detail) || !isset($detail['orIntv'], $detail['mois']) || $detail['orIntv'] === "-") {
                    return null;
                }

                $monthIndex = (int)$detail['mois'] - 1;
                $year = $detail['annee'] ?? '';
                $monthKey = sprintf('%04d-%02d', $year, $monthIndex + 1);

                if (array_search($monthKey, array_column($selectedMonths, 'key')) !== false) {
                    return [
                        'month' => $months[$monthIndex] ?? '',
                        'year' => $year,
                        'details' => $detail,
                    ];
                }

                return null;
            }, $moisDetails));

            return [
                'libsuc' => $item->getLibsuc() ?? '',
                'libserv' => $item->getLibServ() ?? '',
                'idmat' => $item->getIdMat() ?? '',
                'marqueMat' => $item->getMarqueMat() ?? '',
                'typemat' => $item->getTypeMat() ?? '',
                'numserie' => $item->getNumSerie() ?? '',
                'numparc' => $item->getNumParc() ?? '',
                'casier' => $item->getCasier() ?? '',
                'filteredMonths' => array_values($filteredMonths),
            ];
        }, $data);

        return [
            'preparedData' => $preparedData,
            'uniqueMonths' => $selectedMonths,
        ];
    }

    private function getSelectedMonths(array $months, int $currentMonth, int $currentYear, int $selectedOption): array
    {
        $selectedMonths = [];

        switch ($selectedOption) {
            case 3: // 3 mois suivant
            case 6: // 6 mois suivant
                $monthsCount = $selectedOption === 3 ? 4 : 7;
                for ($i = 0; $i < $monthsCount; $i++) {
                    $selectedMonths[] = $this->generateMonthData($months, $currentMonth, $currentYear, $i);
                }
                // Compléter avec les mois précédents
                for ($i = -1; count($selectedMonths) < 12; $i--) {
                    array_unshift($selectedMonths, $this->generateMonthData($months, $currentMonth, $currentYear, $i));
                }
                break;

            case 9: // Année en cours
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = [
                        'month' => $months[$i],
                        'year' => $currentYear,
                        'key' => sprintf('%04d-%02d', $currentYear, $i + 1),
                    ];
                }
                break;

            case 11: // Année suivante
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = [
                        'month' => $months[$i],
                        'year' => $currentYear + 1,
                        'key' => sprintf('%04d-%02d', $currentYear + 1, $i + 1),
                    ];
                }
                break;
            case 12: // 12 mois suivant (à partir du mois suivant le mois courant)
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = $this->generateMonthData($months, $currentMonth, $currentYear, $i);
                }
                break;

            case 13: // 12 mois précédent (jusqu'au mois précédent le mois courant)
                for ($i = -11; $i <= 0; $i++) {
                    $selectedMonths[] = $this->generateMonthData($months, $currentMonth, $currentYear, $i);
                }
                break;

            case 14: // Année précédente
                $previousYear = $currentYear - 1;
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = [
                        'month' => $months[$i],
                        'year' => $previousYear,
                        'key' => sprintf('%04d-%02d', $previousYear, $i + 1),
                    ];
                }
                break;
        }

        return $selectedMonths;
    }

    private function generateMonthData(array $months, int $currentMonth, int $currentYear, int $offset): array
    {
        $totalMonths = $currentMonth + $offset;
        $monthIndex = ($totalMonths % 12 + 12) % 12; // Assure un index valide entre 0-11
        $year = $currentYear + intdiv($totalMonths, 12);

        if ($totalMonths < 0 && $monthIndex > $currentMonth) {
            $year--; // Si l'offset est négatif et que le mois calculé est après le mois courant, ajuste l'année.
        }

        return [
            'month' => $months[$monthIndex],
            'year' => $year,
            'key' => sprintf('%04d-%02d', $year, $monthIndex + 1),
        ];
    }
}
