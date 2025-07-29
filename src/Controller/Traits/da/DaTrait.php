<?php

namespace App\Controller\Traits\da;

use App\Controller\Traits\lienGenerique;
use DateTime;
use App\Entity\da\DaValider;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use App\Service\genererPdf\GenererPdfDa;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Model\magasin\MagasinListeOrLivrerModel;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DaTrait
{
    use lienGenerique;

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

    private function statutBc(?string $ref, string $numDit, string $numDa, ?string $designation, ?string $numeroOr): ?string
    {

        $daValider = $this->getDaValider($numDa, $numDit, $ref, $designation);

        if ($daValider == null) {
            return '';
        };
        $statutBc = $daValider->getStatutCde();

        if ($numeroOr == null) {
            return $statutBc;
        }


        $situationCde = $this->daModel->getSituationCde($ref, $numDit, $numDa, $designation, $numeroOr);
        $statutDaIntanert = [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE
        ];
        $statutDa = $this->daRepository->getStatutDa($numDa);
        if (in_array($statutDa, $statutDaIntanert)) {
            return '';
        }

        $numcde = array_key_exists(0, $situationCde) ? $situationCde[0]['num_cde'] : '';
        $bcExiste = $this->daSoumissionBcRepository->bcExists($numcde);
        $statutSoumissionBc = self::$em->getRepository(DaSoumissionBc::class)->getStatut($numcde);

        $qte = $this->daModel->getEvolutionQte($numDit, $numDa, $ref, $designation, $numeroOr);
        [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre] = $this->evaluerQuantites($qte);

        $this->updateInfoOR($numDit, $daValider);
        $this->updateSituationCdeDansDaValider($situationCde, $daValider, $numcde);
        $this->updateQteCdeDansDaValider($qte, $daValider);

        $statutBcDw = [
            DaSoumissionBc::STATUT_SOUMISSION,
            DaSoumissionBc::STATUT_A_VALIDER_DA,
            DaSoumissionBc::STATUT_VALIDE,
            DaSoumissionBc::STATUT_CLOTURE,
            DaSoumissionBc::STATUT_REFUSE
        ];

        if ($this->doitGenererBc($situationCde, $statutDa, $daValider->getStatutOr())) {
            return 'A générer';
        }

        if (!$this->aSituationCde($situationCde)) {
            return $statutBc;
        }

        if ($this->doitEditerBc($situationCde)) {
            return 'A éditer';
        }

        if ($this->doitSoumettreBc($situationCde, $bcExiste, $statutBc, $statutBcDw)) {
            return 'A soumettre à validation';
        }

        if ($this->doitEnvoyerBc($situationCde, $statutBc, $daValider, $statutSoumissionBc)) {
            return 'A envoyer au fournisseur';
        }

        if ($partiellementDispo) {
            return 'Partiellement dispo';
        }

        if ($completNonLivrer) {
            return 'Complet non livré';
        }

        if ($tousLivres) {
            return 'Tous livrés';
        }

        if ($partiellementLivre) {
            return 'Partiellement livré';
        }

        if ($daValider->getBcEnvoyerFournisseur()) {
            return 'BC envoyé au fournisseur';
        }

        return $statutSoumissionBc;
    }

    private function aSituationCde(array $situationCde): bool
    {
        return array_key_exists(0, $situationCde);
    }

    private function doitGenererBc(array $situationCde, string $statutDa, ?string $statutOr): bool
    {
        $daValide = $statutDa === DemandeAppro::STATUT_VALIDE;
        $orValide = $statutOr === DitOrsSoumisAValidation::STATUT_VALIDE;

        // Si aucune situation de commande n'est présente
        if (empty($situationCde)) {
            return $daValide && $orValide;
        }

        // Si une situation existe mais sans numéro de commande
        $numCdeVide = empty($situationCde[0]['num_cde'] ?? null);


        return $numCdeVide && $daValide && $orValide;
    }


    private function doitEditerBc(array $situationCde): bool
    {
        // numero de commande existe && ... && position terminer
        return (int)$situationCde[0]['num_cde'] > 0
            && $situationCde[0]['slor_natcm'] === 'C'
            &&
            ($situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
    }

    private function doitSoumettreBc(array $situationCde, bool $bcExiste, ?string $statutBc, array $statutBcDw): bool
    {
        // numero de commande existe && ... && position editer && BC n'est pas encore soumis
        return (int)$situationCde[0]['num_cde'] > 0
            && $situationCde[0]['slor_natcm'] === 'C'
            && $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
            && !in_array($statutBc, $statutBcDw)
            && !$bcExiste;
    }

    private function doitEnvoyerBc(array $situationCde, ?string $statutBc, DaValider $daValider, string $statutSoumissionBc): bool
    {
        // numero de commande existe && ... && position editer && BC n'est pas encore soumis
        return $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
            && in_array($statutSoumissionBc, [DaSoumissionBc::STATUT_VALIDE, DaSoumissionBc::STATUT_CLOTURE])
            && !$daValider->getBcEnvoyerFournisseur();
    }

    private function evaluerQuantites(array $qte): array
    {
        if (empty($qte)) {
            return [false, false, false, false];
        }

        $q = $qte[0];
        $qteDem = (int)$q['qte_dem'];
        $qteALivrer = (int)$q['qte_a_livrer'];
        $qteLivee = (int)$q['qte_livee'];

        $partiellementDispo = $qteDem != $qteALivrer && $qteLivee == 0 && $qteALivrer > 0;
        $completNonLivrer = ($qteDem == $qteALivrer && $qteLivee < $qteDem) ||
            ($qteALivrer > 0 && $qteDem == ($qteALivrer + $qteLivee));
        $tousLivres = $qteDem == $qteLivee && $qteDem != 0;
        $partiellementLivre = $qteLivee > 0 && $qteLivee != $qteDem && $qteDem > ($qteLivee + $qteALivrer);

        return [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre];
    }


    private function updateQteCdeDansDaValider(array $qte, DaValider $daValider): void
    {
        if (!empty($qte)) {
            $q = $qte[0];
            $qteDem = (int)$q['qte_dem'];
            $qteALivrer = (int)$q['qte_a_livrer'];
            $qteLivee = (int)$q['qte_livee'];
            $qteReliquat = (int)$q['qte_reliquat']; // quantiter en attente
            $qteDispo = (int)$q['qte_reliquat'];

            $daValider
                ->setQteEnAttent($qteReliquat)
                ->setQteALivrer($qteALivrer)
                ->setQteLivrer($qteLivee)
                ->setQteDispo($qteDispo)
            ;
        }
    }


    private function updateSituationCdeDansDaValider(array $situationCde, DaValider $daValider, ?string $numcde): void
    {
        if (!empty($situationCde)) {
            $positionBc = array_key_exists(0, $situationCde) ? $situationCde[0]['position_bc'] : '';
            $daValider->setPositionBc($positionBc)
                ->setNumeroCde($numcde);
        }
    }

    private function updateInfoOR(string $numDit, DaValider $daValider)
    {
        [$numOr, $statutOr] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($numDit);
        $datePlanningOr = $this->getDatePlannigOr($numOr);

        $daValider
            ->setNumeroOr($numOr)
            ->setDatePlannigOr($datePlanningOr)
        ;

        if ($daValider->getStatutOr() != DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION) {
            $daValider->setStatutOr($statutOr);
        }
    }

    private function getDatePlannigOr(?string $numOr)
    {
        if (!is_null($numOr)) {
            $magasinListeOrLivrerModel = new MagasinListeOrLivrerModel();
            $data = $magasinListeOrLivrerModel->getDatePlanningPourDa($numOr);

            if (!empty($data) && !empty($data[0]['dateplanning'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d', $data[0]['dateplanning']);
            }
        }

        return $dateObj ?? null;
    }

    private function getDaValider(string $numDa, string $numDit,  string $ref, string $designation): ?DaValider
    {
        $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMax($numDa);
        $conditionDeRecuperation = [
            'numeroDemandeAppro' => $numDa,
            'numeroDemandeDit' => $numDit,
            'artRefp' => $ref,
            'artDesi' => $designation,
            'numeroVersion' => $numeroVersionMax
        ];
        return $this->daValiderRepository->findOneBy($conditionDeRecuperation);
    }


    private function creationPdf(string $numDa, int $numeroVersionMax)
    {
        $genererPdfDa = new GenererPdfDa();

        $dals = $this->demandeApproLRepository->findBy([
            'numeroDemandeAppro' => $numDa,
            'numeroVersion' => $numeroVersionMax,
            'deleted' => false // On récupère les DALs avec version max et non supprimés de la DA
        ]);

        foreach ($dals as $dal) {
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroLigne' => $dal->getNumeroLigne()]);
            $dal->setDemandeApproLR(new ArrayCollection($dalrs));
        }

        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);

        $genererPdfDa->genererPdf($dit, $da, $dals);
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
        $filePath = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa/$fileName";
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
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax, 'deleted' => false]); // On récupère les DALs avec version max et non supprimés de la DA

        $donnerExcels = [];
        foreach ($dals as $dal) {
            $donnerExcel = $dal;
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroLigne' => $dal->getNumeroLigne()]);
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
                ->setStatutOr($daValider->getNumeroOr() ? DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION : DitOrsSoumisAValidation::STATUT_VIDE)
                ->setOrResoumettre($daValider->getNumeroOr() ? true : $daValider->getOrResoumettre())
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
            $dalr->getNumeroLigne(),
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
            $dalr->getNumeroLigne(),
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

    private function getAllDAFile($tab): array
    {
        return [
            'BA'    => [
                'type'       => "Bon d'achat",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-ba',
                'fichiers'   => $this->normalizePaths($tab['baPath']),
            ],
            'OR'    => [
                'type'       => 'Ordre de réparation',
                'icon'       => 'fa-solid fa-wrench',
                'colorClass' => 'border-left-or',
                'fichiers'   => $this->normalizePathsForOneFile($tab['orPath'], 'numeroOr'),
            ],
            'BC'    => [
                'type'       => 'Bon de commande',
                'icon'       => 'fa-solid fa-file-circle-check',
                'colorClass' => 'border-left-bc',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['bcPath'], 'numeroBc'),
            ],
            'FACBL' => [
                'type'       => 'Facture / Bon de livraison',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fichiers'   => $this->normalizePaths($tab['facblPath']),
            ],
        ];
    }

    private function normalizePaths($paths): array
    {
        if ($paths === '-' || empty($paths)) {
            return [];
        }

        if (!is_array($paths)) {
            $paths = [$paths];
        }

        return array_map(function ($path) {
            return [
                'nom'  => pathinfo($path, PATHINFO_FILENAME),
                'path' => $path
            ];
        }, $paths);
    }

    private function normalizePathsForOneFile($doc, string $numKey): array
    {
        $tabReturn = [];

        if ($doc !== '-' && !empty($doc)) {
            $tabReturn[] = [
                'nom'  => $doc[$numKey],
                'path' => $doc['path']
            ];
        }

        return $tabReturn;
    }

    private function normalizePathsForManyFiles($allDocs, string $numKey): array
    {
        if ($allDocs === '-' || empty($allDocs)) {
            return [];
        }

        return array_map(function ($doc) use ($numKey) {
            return [
                'nom'  => $doc[$numKey],
                'path' => $doc['path']
            ];
        }, $allDocs);
    }

    /** 
     * Obtenir l'url du bon d'achat
     */
    private function getBaPath(DemandeAppro $demandeAppro): string
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        if (in_array($demandeAppro->getStatutDal(), [DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_TERMINER])) {
            return $_ENV['BASE_PATH_FICHIER_COURT'] . "/da/$numDa/$numDa.pdf";
        }
        return "-";
    }

    /** 
     * Obtenir l'url de l'ordre de réparation
     */
    private function getOrPath(DemandeAppro $demandeAppro)
    {
        $numeroDit = $demandeAppro->getNumeroDemandeDit();
        $ditOrsSoumis = $this->ditOrsSoumisAValidationRepository->findDerniereVersionByNumeroDit($numeroDit);
        $numeroOr = !empty($ditOrsSoumis) ? $ditOrsSoumis[0]->getNumeroOR() : '';
        $statutOr = !empty($ditOrsSoumis) ? $ditOrsSoumis[0]->getStatut() : '';
        if ($statutOr == 'Validé') {
            $result = $this->dossierInterventionAtelierModel->findCheminOrVersionMax($numeroOr);
            return [
                'numeroOr' => $numeroOr,
                'path'     => $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $result['chemin']
            ];
        }
        return "-";
    }

    /** 
     * Obtenir l'url du bon de commande
     */
    private function getBcPath(DemandeAppro $demandeAppro)
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $allDocs = $this->dwBcApproRepository->getPathAndNumeroBCByNumDa($numDa);


        if (!empty($allDocs)) {
            return array_map(function ($doc) {
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return "-";
    }

    /** 
     * Obtenir l'url du bon de livraison + facture
     */
    private function getFacBlPath(DemandeAppro $demandeAppro): string
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $path = $this->dwFacBlRepository->getPathByNumDa($numDa);

        if ($path) {
            return $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $path;
        }

        return "-";
    }
}
