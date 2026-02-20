<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Model\dw\DossierInterventionAtelierModel;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use DateTime;

class DaService
{
    private EntityManagerInterface $em;
    private DemandeApproRepository $demandeApproRepository;
    private DaObservationRepository $daObservationRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private FileUploaderForDAService $daFileUploader;

    public function __construct(EntityManagerInterface $em, FileUploaderForDAService $daFileUploader)
    {
        $this->em                       = $em;
        $this->daFileUploader           = $daFileUploader;
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->daObservationRepository  = $em->getRepository(DaObservation::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * Permet de calculer le nombre de jours disponibles avant la date de fin souhaitée
     *
     * @return int Nombre de jours disponibles (positif si la date n'est pas encore passée, négatif si elle l'est)
     */
    public function getJoursRestants($dal): int
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
     * @param string         $numDa       le numéro de la DA
     * @param string         $observation l'Observation à insérer
     * @param string         $username    le nom de l'utilisateur
     * @param UploadedFile[] $files       les fichiers à uploader
     * 
     * @return void
     */
    public function insertionObservation(string $numDa, string $observation, string $username, ?array $files = null): void
    {
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);

        $daObservation = new DaObservation();

        $daObservation
            ->setObservation($text)
            ->setNumDa($numDa)
            ->setUtilisateur($username)
        ;

        if ($files) {
            $fileNames = $this->daFileUploader->uploadMultipleDaFiles($files, $numDa, FileUploaderForDAService::FILE_TYPE["OBSERVATION"]);
            $daObservation->setFileNames($fileNames);
        }

        $this->em->persist($daObservation);
        $this->em->flush();
    }

    /**
     * Récupère les lignes d'une Demande d'Achat en tenant compte des rectifications utilisateur (DALR).
     *
     * @param iterable<DemandeApproL> $lignesDAL les lignes de DAL de la DA
     *
     * @return array
     */
    public function getLignesRectifiees(iterable $lignesDAL): array
    {
        $resultats = [];

        foreach ($lignesDAL as $ligneDAL) {
            /** @var iterable<DemandeApproLR> $lignesDalr */
            $lignesDalr = $ligneDAL->getDemandeApproLR();
            if ($lignesDalr->isEmpty()) $resultats[] = $ligneDAL;
            else                        $resultats[] = $lignesDalr->filter(fn(DemandeApproLR $dalr) => $dalr->getChoix())->first();
        }

        return $resultats;
    }

    public function appliquerChangementStatut(DemandeAppro $demandeAppro, string $statut, bool $withFlush = true)
    {
        $demandeAppro->setStatutDal($statut);

        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal($statut);
            /** @var DemandeApproLR $demandeApproLR */
            foreach ($demandeApproL->getDemandeApproLR() as $demandeApproLR) {
                $demandeApproLR->setStatutDal($statut);
                $this->em->persist($demandeApproLR);
            }
            $this->em->persist($demandeApproL);
        }

        $this->em->persist($demandeAppro);

        if ($withFlush) $this->em->flush();
    }

    /** Récupère une demande appro par son id */
    public function getDemandeAppro(int $id): ?DemandeAppro
    {
        return $this->demandeApproRepository->find($id);
    }

    /** Récupère tous les observations d'une DA */
    public function getObservations(string $numDa): array
    {
        return $this->daObservationRepository->findBy(['numDa' => $numDa], ['dateCreation' => 'ASC']);
    }

    /** 
     * Obtenir l'url du bon d'achat
     */
    public function getBaIntranetPath(DemandeAppro $demandeAppro): array
    {
        $item = [];
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $filePath = $_ENV['BASE_PATH_FICHIER_COURT'] . "/da/$numDa/$numDa.pdf";
        $absolutePath = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa/$numDa.pdf";

        if (file_exists($absolutePath)) {
            $item = [
                'nom'  => pathinfo($filePath, PATHINFO_FILENAME),
                'path' => $filePath,
            ];
        }
        return $item;
    }

    /** 
     * Obtenir l'url des devis et pièces jointes émis dans les lignes de la DA
     */
    public function getDevisPjPathDaLine(DemandeAppro $demandeAppro): array
    {
        $items = [];

        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $pjDals = $this->demandeApproLRepository->findAttachmentsByNumeroDA($numDa);
        $pjDalrs = $this->demandeApproLRRepository->findAttachmentsByNumeroDA($numDa);

        /** 
         * Fusionner les résultats des deux tables
         * @var array<int, array{numeroDemandeAppro: string, fileNames: array}>
         **/
        $allRows = array_merge($pjDals, $pjDalrs);

        foreach ($allRows as $row) {
            $files = $row['fileNames'];
            foreach ($files as $fileName) {
                $items[] = [
                    'nomPj' => $fileName,
                    'path'  => "{$_ENV['BASE_PATH_FICHIER_COURT']}/da/$numDa/$fileName",
                ];
            }
        }
        return $items;
    }

    /** 
     * Obtenir l'url des devis et pièces jointes émis dans l'observation
     */
    public function getDevisPjPathObservation(DemandeAppro $demandeAppro): array
    {
        $items = [];
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $pjs = $this->daObservationRepository->findAttachmentsByNumeroDA($numDa);

        foreach ($pjs as $row) {
            $files = $row['fileNames'];
            foreach ($files as $fileName) {
                $items[] = [
                    'nomPj' => $fileName,
                    'path'  => "{$_ENV['BASE_PATH_FICHIER_COURT']}/da/$numDa/$fileName",
                ];
            }
        }
        return $items;
    }

    /** 
     * Obtenir l'url de la dernière ordre de réparation validé liée à la DA
     */
    public function getOrPath(DemandeAppro $demandeAppro)
    {
        $result = [];
        $dataOR = (new DossierInterventionAtelierModel)->findCheminOrDernierValide($demandeAppro->getNumeroDemandeDit(), $demandeAppro->getNumeroDemandeAppro());

        if ($dataOR) {
            $result = [
                'numeroOr' => $dataOR['numero'],
                'path'     => $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $dataOR['chemin']
            ];
        }

        return $result;
    }
}
