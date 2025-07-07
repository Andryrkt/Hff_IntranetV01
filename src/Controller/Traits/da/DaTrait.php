<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DaValider;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    private function statutBc(?string $ref, string $numDit, ?string $designation)
    {
        $situationCde = $this->daModel->getSituationCde($ref, $numDit, $designation);

        $statutDa = $this->daRepository->getStatut($numDit);

        $statutOr = $this->ditOrsSoumisAValidationRepository->getStatut($numDit);
        $numcde = array_key_exists(0, $situationCde) ? $situationCde[0]['num_cde'] : '';
        $bcExiste = $this->daSoumissionBcRepository->bcExists($numcde);

        $statutBc = $this->daSoumissionBcRepository->getStatut($numcde);

        $qte = $this->daModel->getEvolutionQte($numDit, true, $ref, $designation);
        if (!empty($qte)) {
            $partiellementDispo = $qte[0]['qte_dem'] != $qte[0]['qte_a_livrer'] && $qte[0]['qte_livee'] == 0 && $qte[0]['qte_a_livrer'] > 0;
            $completNonLivrer = ($qte[0]['qte_dem'] == $qte[0]['qte_a_livrer'] && $qte[0]['qte_livee'] < $qte[0]['qte_dem']) || ($qte[0]['qte_a_livrer'] > 0 && $qte[0]['qte_dem'] == ($qte[0]['qte_a_livrer'] + $qte[0]['qte_livee']));
            $tousLivres = $qte[0]['qte_dem'] ==  $qte[0]['qte_livee'] && $qte[0]['qte_dem'] != '' && $qte[0]['qte_livee'] != '';
            $partiellementLivre = $qte[0]['qte_livee'] > 0 && $qte[0]['qte_livee'] != $qte[0]['qte_dem'] && $qte[0]['qte_dem'] > ($qte[0]['qte_livee'] + $qte[0]['qte_a_livrer']);
        }

        $statut_bc = '';
        if (!array_key_exists(0, $situationCde)) {
            $statut_bc = $statutBc;
        } elseif ($situationCde[0]['num_cde'] == '' && $statutDa == DemandeAppro::STATUT_VALIDE && $statutOr == 'Validé') {
            $statut_bc = 'A générer';
        } elseif ((int)$situationCde[0]['num_cde'] > 0 && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'TE') {
            $statut_bc = 'A éditer';
        } elseif ((int)$situationCde[0]['num_cde'] > 0 && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'ED' && !$bcExiste) {
            $statut_bc = 'A soumettre à validation';
        } elseif ($situationCde[0]['position_bc'] == 'ED' && $statutBc == 'Validé') {
            $statut_bc = 'A envoyer au fournisseur';
        } elseif ($partiellementDispo) {
            $statut_bc = 'Partiellement dispo';
        } elseif ($completNonLivrer) {
            $statut_bc = 'Complet non livré';
        } elseif ($tousLivres) {
            $statut_bc = 'Tous livrés';
        } elseif ($partiellementLivre) {
            $statut_bc = 'Partiellement livré';
        } else {
            $statut_bc = $statutBc;
        }

        return $statut_bc;
    }

    // private function statutBcCde(?string $ref, string $numDit, ?string $numCde)
    // {
    //     $situationCde = $this->daModel->getSituationCde($ref, $numDit);
    //     $statutDa = $this->demandeApproRepository->getStatut($numDit);
    //     $statutOr = $this->ditOrsSoumisAValidationRepository->getStatut($numDit);

    //     $bcExiste = $this->daSoumissionBcRepository->bcExists($numCde);

    //     $statutBc = $this->daSoumissionBcRepository->getStatut($numCde);
    //     $statut_bc = '';
    //     if (!array_key_exists(0, $situationCde)) {
    //         $statut_bc = $statutBc;
    //     } elseif ($situationCde[0]['num_cde'] == '' && $statutDa == DemandeAppro::STATUT_VALIDE && $statutOr == 'Validé') {
    //         $statut_bc = 'A générer';
    //     } elseif ((int)$situationCde[0]['num_cde'] <> '' && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'TE') {
    //         $statut_bc = 'A éditer';
    //     } elseif ($situationCde[0]['position_bc'] == 'ED' && $statutBc == 'Validé') {
    //         $statut_bc = 'A envoyer au fournisseur';
    //     }elseif ((int)$situationCde[0]['num_cde'] > 0 && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'ED' && !$bcExiste) {
    //         $statut_bc = 'A soumettre à validation';
    //     }  else {
    //         $statut_bc = $statutBc;
    //     }

    //     return $statut_bc;
    // }

    private function creationPdf(string $numDa, int $numeroVersionMax)
    {
        $daValiders = $this->daValiderRepository->findBy([
            'numeroDemandeAppro' => $numDa,
            'numeroVersion' => $numeroVersionMax
        ]);

        $dit = $this->ditRepository->findOneBy(['numeroDemandeDit' => $daValiders[0]->getNumeroDemandeDit()]);
    }

    private function SommeTotal($daValiders): float
    {
        $somme = 0.0;
        foreach ($daValiders as $daValider) {
            $somme += (float)$daValider->getTotal();
        }
        return $somme;
    }

    private function creationExcel(string $numDa, int $numeroVersionMax): array
    {
        //recupération des donnée
        $donnerExcels = $this->recuperationRectificationDonnee($numDa, $numeroVersionMax);

        //enregistrement des données dans DaValider
        $this->enregistrerDonneeDansDaValide($donnerExcels);

        //creation PDF
        $this->creationPdf($numDa, $numeroVersionMax);

        // Convertir les entités en tableau de données
        $dataExel = $this->transformationEnTableauAvecEntet($donnerExcels);

        //creation du fichier excel
        $date = new DateTime();
        $formattedDate = $date->format('Ymd_His');
        $fileName = $numDa . '_' . $formattedDate . '.xlsx';
        $filePath = $_ENV['BASE_PATH_FICHIER'] . '/da/ba/' . $fileName;
        $this->excelService->createSpreadsheetEnregistrer($dataExel, $filePath);

        return [
            'fileName' => $fileName,
            'filePath' => $filePath
        ];
    }

    private function transformationEnTableauAvecEntet($entities): array
    {
        $data = [];
        $data[] = ['constructeur', 'reference', 'quantité', '', 'designation', 'PU'];

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getArtConstp(),
                $entity->getArtRefp(),
                $entity->getQteDem(),
                '',
                $entity->getArtRefp() == 'ST' ? $entity->getArtDesi() : '',
                $entity->getArtRefp() == 'ST' ? $entity->getPrixUnitaire() : '',
            ];
        }

        return $data;
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
                        break;
                    }
                }
            }
            $donnerExcels[] = $donnerExcel;
        }

        return $donnerExcels;
    }

    private function enregistrerDonneeDansDaValide($donnees)
    {
        $em = self::getEntity();
        foreach ($donnees as $donnee) {
            $daValider = new DaValider;

            /** @var DemandeAppro $da l'entité de la demande appro correspondant au numero demandeAppro du donnée (DAL ou DALR) */
            $da = $em->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $donnee->getNumeroDemandeAppro()]);

            $numeroVersionMax = $em->getRepository(DaValider::class)->getNumeroVersionMax($da->getNumeroDemandeAppro());
            $nivUrgence = $em->getRepository(DemandeIntervention::class)->getNiveauUrgence($da->getNumeroDemandeDit());
            $daValider
                ->setNiveauUrgence($nivUrgence) // niveau d'urgence du DIT attaché à la DA
                ->setNumeroVersion($this->autoIncrementForDa($numeroVersionMax)) // numero de version de DaValider
            ;

            $daValider->enregistrerDa($da); // enregistrement pour DA

            if ($donnee instanceof DemandeApproL) {
                $daValider->enregistrerDal($donnee); // enregistrement pour DAL
            } else if ($donnee instanceof DemandeApproLR) {
                $daValider->enregistrerDalr($donnee); // enregistrement pour DALR
            }

            $em->persist($daValider);
        }

        $em->flush();
    }

    /**
     * TRAITEMENT DES FICHIER UPLOAD
     * (copier le fichier uploadé dans une répertoire et le donner un nom)
     */
    private function uploadFileTo(UploadedFile $file, string $fileName, string $destination)
    {
        // Assurer que le répertoire existe
        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD (pièces jointes de la DAL)
     */
    private function uploadPJForDal(UploadedFile $file, DemandeApproL $dal, int $i): string
    {
        $fileName = sprintf(
            'PJ_%s_%s_%s.%s',
            date("YmdHis"),
            $dal->getNumeroLigne(),
            $i,
            $file->getClientOriginalExtension()
        ); // Exemple: PJ_20250623121403_3_1.pdf

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/da/' . $dal->getNumeroDemandeAppro() . '/';

        $this->uploadFileTo($file, $fileName, $destination);

        return $fileName;
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD (pièces jointes de la DAL)
     */
    private function uploadPJForDalr(UploadedFile $file, DemandeApproLR $dalr, int $i): string
    {
        $fileName = sprintf(
            'PJ_%s_%s%s_%s.%s',
            date("YmdHis"),
            $dalr->getNumeroLigneDem(),
            $dalr->getNumLigneTableau(),
            $i,
            $file->getClientOriginalExtension()
        ); // Exemple: PJ_20250623121403_34_1.pdf

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/da/' . $dalr->getNumeroDemandeAppro() . '/';

        $this->uploadFileTo($file, $fileName, $destination);

        return $fileName;
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD (fiche technique de la DALR)
     */
    private function uploadFTForDalr(UploadedFile $file, DemandeApproLR $dalr)
    {
        $fileName = sprintf(
            'FT_%s_%s_%s.%s',
            date("YmdHis"),
            $dalr->getNumeroLigneDem(),
            $dalr->getNumLigneTableau(),
            $file->getClientOriginalExtension()
        ); // Exemple: FT_20250623121403_2_4.pdf

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/da/' . $dalr->getNumeroDemandeAppro() . '/';

        $this->uploadFileTo($file, $fileName, $destination);

        $dalr->setNomFicheTechnique($fileName);
    }

    private function ajoutJour(int $jourAjouter): DateTime
    {
        $date = new DateTime();

        // Compteur pour les jours ouvrables ajoutés
        $joursOuvrablesAjoutes = 0;

        // Ajouter des jours jusqu'à obtenir 3 jours ouvrables
        while ($joursOuvrablesAjoutes < $jourAjouter) {
            // Ajouter un jour
            $date->modify('+1 day');

            // Vérifier si le jour actuel est un jour ouvrable (ni samedi ni dimanche)
            if ($date->format('N') < 6) { // 'N' donne 1 (lundi) à 7 (dimanche)
                $joursOuvrablesAjoutes++;
            }
        }
        return $date;
    }

    private function autoIncrementForDa(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }
}
