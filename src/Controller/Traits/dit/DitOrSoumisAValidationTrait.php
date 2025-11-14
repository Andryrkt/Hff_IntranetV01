<?php

namespace App\Controller\Traits\dit;

use DateTime;
use Exception;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\FormInterface;
use App\Entity\dit\DitOrsSoumisAValidation;

trait DitOrSoumisAValidationTrait
{

    /**
     * Upload un fichier et retourne le chemin du fichier enregistré si c'est un PDF, sinon null.
     *
     * @param UploadedFile $file
     * @param DitFacture $ditfacture
     * @param string $fieldName
     * @param int $index
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function uploadFile($file,  $ditfacture, string $fieldName, int $index, string $suffix): ?string
    {
        // Validation des extensions et types MIME
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];

        if (
            !$file->isValid() ||
            !in_array(strtolower($file->getClientOriginalExtension()), $allowedExtensions, true) ||
            !in_array($file->getMimeType(), $allowedMimeTypes, true)
        ) {
            throw new \InvalidArgumentException("Type de fichier non autorisé pour le champ $fieldName.");
        }

        // Générer un nom de fichier sécurisé et unique

        $fileName = sprintf(
            'oRValidation_%s-%s_%02d#%s.%s',
            $ditfacture->getNumeroOR(),
            $ditfacture->getNumeroVersion(),
            $index,
            $suffix,
            $file->guessExtension()
        );

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/vor/fichier/';

        // Assurer que le répertoire existe
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        // Retourner le chemin complet du fichier si c'est un PDF, sinon null
        if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
            return $destination . $fileName;
        }

        return null;
    }
    /**
     * Envoie des pièces jointes et fusionne les PDF
     */
    private function envoiePieceJoint(
        FormInterface $form,
        $ditfacture,
        $fusionPdf,
        $suffix,
        $mainPdf
    ): void {
        $pdfFiles = [];


        // Vérifier que le fichier principal existe avant de l'ajouter
        if (!file_exists($mainPdf)) {
            throw new \RuntimeException('Le fichier PDF principal n\'existe pas.');
        }

        // Ajouter le fichier PDF principal en tête du tableau
        array_unshift($pdfFiles, $mainPdf);

        // Récupérer tous les champs de fichiers du formulaire
        $fileFields = $form->all();

        foreach ($fileFields as $fieldName => $field) {
            if (preg_match('/^pieceJoint\d{2}$/', $fieldName)) {
                /** @var UploadedFile|null $file */
                $file = $field->getData();
                if ($file !== null) {
                    // Extraire l'index du champ (e.g., pieceJoint01 -> 1)
                    if (preg_match('/^pieceJoint(\d{2})$/', $fieldName, $matches)) {
                        $index = (int)$matches[1];
                        $pdfPath = $this->uploadFile($file, $ditfacture, $fieldName, $index, $suffix);
                        if ($pdfPath !== null) {
                            $pdfFiles[] = $pdfPath;
                        }
                    }
                }
            }
        }

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $mainPdf;

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $this->ConvertirLesPdf($pdfFiles);
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
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

    private function autoIncrement($num)
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }

    private function calculeSommeMontant($orSoumisValidataion)
    {
        $totalRecapOr = [
            'total' => 'TOTAL',
            'montant_itv' => 0,
            'montant_piece' => 0,
            'montant_mo' => 0,
            'montant_achats_locaux' => 0,
            'montant_lubrifiants' => 0,
            'montant_frais_divers' => 0,
        ];
        foreach ($orSoumisValidataion as $orSoumis) {
            // Faire la somme des montants et les stocker dans le tableau
            $totalRecapOr['montant_itv'] += $orSoumis->getMontantItv();
            $totalRecapOr['montant_piece'] += $orSoumis->getMontantPiece();
            $totalRecapOr['montant_mo'] += $orSoumis->getMontantMo();
            $totalRecapOr['montant_achats_locaux'] += $orSoumis->getMontantAchatLocaux();
            $totalRecapOr['montant_lubrifiants'] += $orSoumis->getMontantLubrifiants();
            $totalRecapOr['montant_frais_divers'] += $orSoumis->getMontantFraisDivers();
        }

        return $totalRecapOr;
    }

    private function recuperationAvantApres($OrSoumisAvantMax, $OrSoumisAvant)
    {

        if (!empty($OrSoumisAvantMax)) {
            // Trouver les objets manquants par numero d'intervention dans chaque tableau
            $manquantDansOrSoumisAvantMax = $this->objetsManquantsParNumero($OrSoumisAvantMax, $OrSoumisAvant);
            $manquantDansOrSoumisAvant = $this->objetsManquantsParNumero($OrSoumisAvant, $OrSoumisAvantMax);

            // Ajouter les objets manquants dans chaque tableau
            $OrSoumisAvantMax = array_merge($OrSoumisAvantMax, $manquantDansOrSoumisAvantMax);
            $OrSoumisAvant = array_merge($OrSoumisAvant, $manquantDansOrSoumisAvant);

            // Trier les tableaux par numero d'intervention
            $this->trierTableauParNumero($OrSoumisAvantMax);
            $this->trierTableauParNumero($OrSoumisAvant);
        }


        $recapAvantApres = [];

        for ($i = 0; $i < count($OrSoumisAvant); $i++) {

            $itv = $OrSoumisAvant[$i]->getNumeroItv();
            $libelleItv = $OrSoumisAvant[$i]->getLibellelItv();
            $nbLigAp = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getNombreLigneItv() : 0;
            $mttTotalAp = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantItv() : 0;
            $nbLigAv = isset($OrSoumisAvantMax[$i]) ? $OrSoumisAvantMax[$i]->getNombreLigneItv() : 0;
            $mttTotalAv = isset($OrSoumisAvantMax[$i]) ? $OrSoumisAvantMax[$i]->getMontantItv() : 0;

            $recapAvantApres[] = [
                'itv' => $itv,
                'libelleItv' => $libelleItv,
                'datePlanning' => $this->datePlanning($OrSoumisAvant[$i]->getNumeroOR(), $itv),
                'nbLigAv' => $nbLigAv,
                'nbLigAp' => $nbLigAp,
                'mttTotalAv' => $mttTotalAv,
                'mttTotalAp' => $mttTotalAp,
            ];
        }

        return $recapAvantApres;
    }



    private function affectationStatut($recapAvantApres)
    {
        $nombreStatutNouvEtSupp = [
            'nbrNouv' => 0,
            'nbrSupp' => 0,
            'nbrModif' => 0,
            'mttModif' => 0
        ];
        //dump($recapAvantApres);
        foreach ($recapAvantApres as &$value) { // Référence les éléments pour les modifier directement
            if ($value['nbLigAv'] === $value['nbLigAp'] && $value['mttTotalAv'] === $value['mttTotalAp']) {
                $value['statut'] = '';
            } elseif ($value['nbLigAv'] !== 0 && $value['mttTotalAv'] !== 0.0 && $value['nbLigAp'] === 0 && $value['mttTotalAp'] === 0.0) {
                //dump($value);
                $value['statut'] = 'Supp';
                $nombreStatutNouvEtSupp['nbrSupp']++;
            } elseif (($value['nbLigAv'] === 0 || $value['nbLigAv'] === '') && $value['mttTotalAv'] === 0.0 || $value['mttTotalAv'] === 0) {
                $value['statut'] = 'Nouv';
                $nombreStatutNouvEtSupp['nbrNouv']++;
            } elseif (($value['nbLigAv'] !== $value['nbLigAp'] || $value['mttTotalAv'] !== $value['mttTotalAp']) && ($value['nbLigAv'] !== 0 || $value['nbLigAv'] !== '' || $value['nbLigAp'] !== 0)) {
                //dump($value);
                $value['statut'] = 'Modif';
                $nombreStatutNouvEtSupp['nbrModif']++;
                $nombreStatutNouvEtSupp['mttModif'] = $nombreStatutNouvEtSupp['mttModif'] + ($value['mttTotalAp'] - $value['mttTotalAv']);
            }
        }
        //dd($recapAvantApres);
        // Retourner le tableau modifié et les statistiques de nouveaux et supprimés
        return [
            'recapAvantApres' => $recapAvantApres,
            'nombreStatutNouvEtSupp' => $nombreStatutNouvEtSupp
        ];
    }


    private function calculeSommeAvantApres($recapAvantApres)
    {
        $totalRecepAvantApres = [
            'premierLigne' => '',
            'deuxiemeLigne' => '',
            'total' => 'TOTAL',
            'totalNbLigAv' => 0,
            'totalNbLigAp' => 0,
            'totalMttTotalAv' => 0,
            'totalMttTotalAp' => 0,
            'dernierLigne' => ''
        ];
        foreach ($recapAvantApres as  $value) {
            $totalRecepAvantApres['totalNbLigAv'] += $value['nbLigAv'] === '' ? 0 : $value['nbLigAv'];
            $totalRecepAvantApres['totalNbLigAp'] += $value['nbLigAp'];
            $totalRecepAvantApres['totalMttTotalAv'] += $value['mttTotalAv'] === '' ? 0 : $value['mttTotalAv'];
            $totalRecepAvantApres['totalMttTotalAp'] += $value['mttTotalAp'];
        }

        return $totalRecepAvantApres;
    }

    private function recapitulationOr($orSoumisValidataion)
    {
        $recapOr = [];
        foreach ($orSoumisValidataion as $orSoumis) {
            $recapOr[] = [
                'itv' => $orSoumis->getNumeroItv(),
                'mttTotal' => $orSoumis->getMontantItv(),
                'mttPieces' => $orSoumis->getMontantPiece(),
                'mttMo' => $orSoumis->getMontantMo(),
                'mttSt' => $orSoumis->getMontantAchatLocaux(),
                'mttLub' => $orSoumis->getMontantLubrifiants(),
                'mttAutres' => $orSoumis->getMontantFraisDivers(),
            ];
        }
        return $recapOr;
    }


    private function montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax)
    {
        $recapAvantApres = $this->recuperationAvantApres($OrSoumisAvantMax, $OrSoumisAvant);
        return [
            'avantApres' => $this->affectationStatut($recapAvantApres)['recapAvantApres'],
            'totalAvantApres' => $this->calculeSommeAvantApres($recapAvantApres),
            'recapOr' => $this->recapitulationOr($orSoumisValidataion),
            'totalRecapOr' => $this->calculeSommeMontant($orSoumisValidataion),
            'nombreStatutNouvEtSupp' => $this->affectationStatut($recapAvantApres)['nombreStatutNouvEtSupp']
        ];
    }

    private function orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis, $numDit)
    {
        /** @var array */
        $pieceFaibleAchat = $this->preparationDesPiecesFaibleAchat($ditInsertionOrSoumis->getNumeroOR());


        $orSoumisValidataion = []; // Tableau pour stocker les objets

        foreach ($orSoumisValidationModel as $orSoumis) {
            // Instancier une nouvelle entité pour chaque entrée du tableau
            $ditInsertionOr = new DitOrsSoumisAValidation();

            $ditInsertionOr
                ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                ->setHeureSoumission($this->getTime())
                ->setDateSoumission(new \DateTime($this->getDatesystem()))
                ->setNumeroOR($ditInsertionOrSoumis->getNumeroOR())
                ->setNumeroItv($orSoumis['numero_itv'])
                ->setNombreLigneItv($orSoumis['nombre_ligne'])
                ->setMontantItv($orSoumis['montant_itv'])
                ->setMontantPiece($orSoumis['montant_piece'])
                ->setMontantMo($orSoumis['montant_mo'])
                ->setMontantAchatLocaux($orSoumis['montant_achats_locaux'])
                ->setMontantFraisDivers($orSoumis['montant_divers'])
                ->setMontantLubrifiants($orSoumis['montant_lubrifiants'])
                ->setLibellelItv($orSoumis['libelle_itv'])
                ->setStatut('Soumis à validation')
                ->setNumeroDit($numDit)
                ->setPieceFaibleActiviteAchat(empty($pieceFaibleAchat) ? false : true)
            ;

            $orSoumisValidataion[] = $ditInsertionOr; // Ajouter l'objet dans le tableau
        }

        return $orSoumisValidataion;
    }

    // Fonction pour trouver les numéros d'intervention manquants
    private function objetsManquantsParNumero($tableauA, $tableauB)
    {
        $manquants = [];
        foreach ($tableauB as $objetB) {
            $trouve = false;
            foreach ($tableauA as $objetA) {
                if ($objetA->estEgalParNumero($objetB)) {
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                $numeroItvExist = $objetB->getNumeroItv() === 0 ? $objetA->getNumeroItv() : $objetB->getNumeroItv();
                $numeroOrExist = $objetB->getNumeroOR() === "" ? $objetA->getNumeroOR() : $objetB->getNumeroOR();
                // Créer un nouvel objet avec uniquement le numero et les autres propriétés à null ou 0
                $nouvelObjet = new DitOrsSoumisAValidation();
                $nouvelObjet->setNumeroOR($numeroOrExist);
                $nouvelObjet->setNumeroItv($numeroItvExist);
                $manquants[] = $nouvelObjet;
            }
        }
        return $manquants;
    }

    // Fonction pour trier les tableaux par numero d'intervention
    private function trierTableauParNumero(&$tableau)
    {
        usort($tableau, function ($a, $b) {
            return strcmp($a->getNumeroItv(), $b->getNumeroItv());
        });
    }

    private function verificationDatePlanning($ditInsertionOrSoumis, $ditOrsoumisAValidationModel): bool
    {
        $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanning1($ditInsertionOrSoumis->getNumeroOR());
        $datePlannig2 = $ditOrsoumisAValidationModel->recupNbDatePlanningVide($ditInsertionOrSoumis->getNumeroOR());

        $aBlocker = false;
        if (empty($datePlannig1)) {
            if ((int)$datePlannig2[0]['nbplanning'] > 0) {
                $aBlocker = true;
            }
        }

        return $aBlocker;
    }

    private function datePlanningInferieurDateDuJour($numOr): bool
    {
        $orRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $nbrOrSoumis = $orRepository->getNbrOrSoumis($numOr); //première soumission
        $estBloquer = $this->ditOrsoumisAValidationModel->getTypeLigne($numOr); //return bloquer si type piece ou pas bloquer  si non

        if ((int)$nbrOrSoumis <= 0 && in_array('bloquer', $estBloquer)) { // si pas encore soumis et c'est une type piece
            $numItvs = $this->ditOrsoumisAValidationModel->getNumItv($numOr);
            $dateDuJour = new DateTime('now');
            foreach ($numItvs as $numItv) {
                $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanningOR1($numOr, $numItv);
                $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanningOR2($numOr, $numItv);
                $datePlanning = empty($datePlannig1) ? new DateTime($datePlannig2[0]['dateplanning2']) : new DateTime($datePlannig1[0]['dateplanning1']);
                if ($datePlanning->format('Y-m-d') < $dateDuJour->format('Y-m-d')) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }
    private function premierSoumissionDatePlanningInferieurDateDuJour($numOr): bool
    {
        $orRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $nbrOrSoumis = $orRepository->getNbrOrSoumis($numOr); //première soumission
        $nbrPieceMagasin = $this->ditOrsoumisAValidationModel->recupNbPieceMagasin($numOr); //nombre de piece magasin

        if ((int)$nbrOrSoumis <= 0 && (int)$nbrPieceMagasin <= 0) { // si pas encore soumis et pas de piece magasin
            $numItvs = $this->ditOrsoumisAValidationModel->getNumItv($numOr);
            $dateDuJour = new DateTime('now');
            foreach ($numItvs as $numItv) {
                $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanningOR1($numOr, $numItv);
                $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanningOR2($numOr, $numItv);
                $datePlanning = empty($datePlannig1) ? new DateTime($datePlannig2[0]['dateplanning2']) : new DateTime($datePlannig1[0]['dateplanning1']);
                if ($datePlanning->format('Y-m-d') < $dateDuJour->format('Y-m-d')) { // date planning est inférieure à la date du jour
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }


    private function datePlanning($numOr, $numItv)
    {
        $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanningOR1($numOr, $numItv);
        $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanningOR2($numOr, $numItv);

        return empty($datePlannig1) ? $datePlannig2[0]['dateplanning2'] : $datePlannig1[0]['dateplanning1'];
    }

    private function nomUtilisateur($em)
    {
        $userId = $this->getSessionService()->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return [
            'nomUtilisateur' => $user->getNomUtilisateur(),
            'mailUtilisateur' => $user->getMail()
        ];
    }
}
