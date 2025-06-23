<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DemandeAppro;
use DateTime;


trait DaTrait
{

    private function ajoutNbrJourRestant($dalDernieresVersions)
    {
        foreach ($dalDernieresVersions as $dal) {
            if ($dal->getStatutDal() != 'Bon d’achats validé') { // si le statut de la DAL est différent de "Bon d’achats validé" 
                // --- 1. Mettre les deux dates à minuit (00:00:00) ---
                $dateFin     = clone $dal->getDateFinSouhaite(); // on clone pour ne pas modifier l'objet de l'entity
                $dateFin->setTime(0, 0, 0);                      // Y-m-d 00:00:00

                $aujourdhui  = new DateTime('today');            // 'today' crée déjà la date du jour à 00:00:00

                // --- 2. Calculer la différence ---
                $interval = $aujourdhui->diff($dateFin);         // toujours positif dans $interval->days
                $days     = $interval->invert ? -$interval->days // invert = 1 si $dateFin est passée
                    :  $interval->days;

                // --- 3. Enregistrer ---
                $dal->setJoursDispo($days);
            }
        }
    }

    private function statutBc(?string $ref, string $numDit, ?string $numCde)
    {
        $situationCde = $this->daModel->getSituationCde($ref, $numDit);
        $statutDa = $this->demandeApproRepository->getStatut($numDit);
        $statutOr = $this->ditOrsSoumisAValidationRepository->getStatut($numDit);

        $bcExiste = $this->daSoumissionBcRepository->bcExists($numCde);

        $statutBc = $this->daSoumissionBcRepository->getStatut($numCde);
        $statut_bc = '';
        if (!array_key_exists(0, $situationCde)) {
            $statut_bc = $statutBc;
        } elseif ($situationCde[0]['num_cde'] == '' && $statutDa == DemandeAppro::STATUT_VALIDE && $statutOr == 'Validé') {
            $statut_bc = 'A générer';
        } elseif ((int)$situationCde[0]['num_cde'] <> '' && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'TE') {
            $statut_bc = 'A éditer';
        } elseif ((int)$situationCde[0]['num_cde'] > 0 && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'ED' && !$bcExiste) {
            $statut_bc = 'A soumettre à validation';
        } elseif ($situationCde[0]['position_bc'] == 'ED' && $statutBc == 'Validé') {
            $statut_bc = 'A envoyer au fournisseur';
        } else {
            $statut_bc = $statutBc;
        }

        return $statut_bc;
    }

    private function recuperationRectificationDonnee(string $numDa, int $numeroVersionMax): array
    {
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        $donnerExcels = [];
        foreach ($dals as $dal) {
            $donnerExcel = $dal;
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroLigneDem' => $dal->getNumeroLigne()]);
            if (!empty($dalrs)) {
                foreach ($dalrs as $dalr) {
                    if ($dalr->getChoix()) {
                        $donnerExcel = $dalr;
                    }
                }
            }
            $donnerExcels[] = $donnerExcel;
        }

        return $donnerExcels;
    }
}
