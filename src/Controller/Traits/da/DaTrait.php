<?php

namespace App\Controller\Traits\da;

use DateTime;
use App\Controller\Traits\EntityManagerAwareTrait;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaObservation;
use App\Entity\da\DaValider;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\da\EmailDaService;
use App\Service\genererPdf\GenererPdfDaAvecDit;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DaTrait
{
    use lienGenerique;
    use EntityManagerAwareTrait;

    private bool $daTraitInitialise = false;

    //=====================================================================================
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private EmailDaService $emailDaService;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaTrait(): void
    {
        // Si déjà exécuté → on sort immédiatement
        if ($this->daTraitInitialise) return;

        $em = $this->getEntityManager();
        $this->emailDaService = new EmailDaService;
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);

        // On note que l'init a été faite
        $this->daTraitInitialise = true;
    }
    //=====================================================================================

    /**
     * Permet de calculer le nombre de jours disponibles avant la date de fin souhaitée
     *
     * @param DemandeApproL $dal
     * @return int Nombre de jours disponibles (positif si la date n'est pas encore passée, négatif si elle l'est)
     */
    public function getJoursRestants(DemandeApproL $dal): int
    {
        // --- 1. Mettre les deux dates à minuit (00:00:00) ---
        $dateFin     = clone $dal->getDateFinSouhaite(); // on clone pour ne pas modifier l'objet de l'entity
        $dateFin->setTime(0, 0, 0);                      // Y-m-d 00:00:00

        $aujourdhui  = new DateTime('today');            // 'today' crée déjà la date du jour à 00:00:00

        // --- 2. Calculer la différence ---
        $interval = $aujourdhui->diff($dateFin);         // toujours positif dans $interval->days
        $days     = $interval->invert ? -$interval->days // invert = 1 si $dateFin est passée
            :  $interval->days;

        // --- 3. Retourner la valeur ---
        return $days;
    }

    /** 
     * Fonction pour l'insertion d'une observation
     * 
     * @param string $observation l'Observation à insérer
     * @param DemandeAppro $demandeAppro l'objet DemandeAppro auquel l'observation est liée
     * 
     * @return void
     */
    private function insertionObservation(string $observation, DemandeAppro $demandeAppro): void
    {
        $em = $this->getEntityManager();

        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);

        $daObservation = new DaObservation();

        $daObservation
            ->setObservation($text)
            ->setNumDa($demandeAppro->getNumeroDemandeAppro())
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
        ;

        $em->persist($daObservation);
        $em->flush();
    }

    /**
     * Récupère les lignes d'une Demande d'Achat en tenant compte des rectifications utilisateur (DALR).
     * Optimisé pour éviter les requêtes en boucle (N+1).
     *
     * @param string $numeroDA le numéro de la Demande d'Achat
     * @param int    $version la version de la Demande d'Achat
     *
     * @return array
     */
    private function getLignesRectifieesDA(string $numeroDA, int $version): array
    {
        // 1. Récupération des lignes DAL (non supprimées)
        /** @var iterable<DemandeApproL> les lignes de DAL non supprimées */
        $lignesDAL = $this->demandeApproLRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
            'numeroVersion'      => $version,
            'deleted'            => false,
        ]);

        // 2. Récupération en une seule requête des DALR associés à la DA
        /** @var iterable<DemandeApproLR> les lignes de DALR correspondant au numéro de la DA */
        $dalrs = $this->demandeApproLRRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
        ]);

        // 3. Indexation des DALR par numéro de ligne, uniquement s'ils sont validés (choix = true)
        $dalrParLigne = [];

        foreach ($dalrs as $dalr) {
            if ($dalr->getChoix()) {
                $dalrParLigne[$dalr->getNumeroLigne()] = $dalr;
            }
        }

        // 4. Construction de la liste finale en remplaçant les DAL par DALR si dispo
        $resultats = [];

        foreach ($lignesDAL as $ligneDAL) {
            $numeroLigne = $ligneDAL->getNumeroLigne(); // numéro de ligne de la DAL
            $resultats[] = $dalrParLigne[$numeroLigne] ?? $ligneDAL;
        }

        return $resultats;
    }

    /** 
     * Méthode pour envoyer une email sur l'observation émis d'une DA avec DIT
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    public function envoyerMailObservationDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $this->emailDaService->envoyerMailObservationDaAvecDit($demandeAppro, $tab);
    }

    /** 
     * Méthode pour envoyer une email sur l'observation émis d'une DA directe
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    public function envoyerMailObservationDaDirect(DemandeAppro $demandeAppro, array $tab): void
    {
        $this->emailDaService->envoyerMailObservationDaDirect($demandeAppro, $tab);
    }







    private function creationPdf(string $numDa, int $numeroVersionMax)
    {
        $genererPdfDaAvecDit = new GenererPdfDaAvecDit();

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

        $genererPdfDaAvecDit->genererPdf($dit, $da);
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
        $donnerExcels = $this->getLignesRectifieesDA($numDa, $numeroVersionMax);

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

    private function enregistrerDonneeDansDaValide($donnees)
    {
        $em = self::getEntity();
        foreach ($donnees as $donnee) {
            $daValider = new DaValider;

            /** @var DemandeAppro $da l'entité de la demande appro correspondant au numero demandeAppro du donnée (DAL ou DALR) */
            $da = $em->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $donnee->getNumeroDemandeAppro()]);

            $numeroVersionMax = $em->getRepository(DaValider::class)->getNumeroVersionMax($da->getNumeroDemandeAppro());
            $nivUrgence = $em->getRepository(DemandeIntervention::class)->getNiveauUrgence($da->getNumeroDemandeDit());
            [$numOr,] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($da->getNumeroDemandeDit());
            $daValider
                ->setNiveauUrgence($nivUrgence) // niveau d'urgence du DIT attaché à la DA
                ->setNumeroVersion($this->autoIncrementForDa($numeroVersionMax)) // numero de version de DaValider
                ->setStatutOr($numOr ? DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION : DitOrsSoumisAValidation::STATUT_VIDE)
                ->setOrResoumettre((bool) $numOr)
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
}
