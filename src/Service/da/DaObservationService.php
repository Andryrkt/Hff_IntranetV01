<?php

namespace App\Service\da;

use App\Entity\da\DaObservation;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\UserData\UserDataService;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use App\Repository\da\DaObservationRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DaObservationService
{
    private EntityManagerInterface $em;
    private DaObservationRepository $daObservationRepository;
    private FileUploaderForDAService $daFileUploader;
    private UserDataService $userDataService;

    public function __construct(EntityManagerInterface $em, FileUploaderForDAService $daFileUploader, UserDataService $userDataService)
    {
        $this->em                      = $em;
        $this->daFileUploader          = $daFileUploader;
        $this->userDataService         = $userDataService;
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }

    /**
     * Fonction pour l'insertion d'une observation
     *
     * @param string         $numDa       le numéro de la DA
     * @param string         $observation l'Observation à insérer
     * @param UploadedFile[] $files       les fichiers à uploader
     *
     * @return void
     */
    public function insertionObservation(string $numDa, string $observation, ?array $files = null): void
    {
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);

        $daObservation = new DaObservation();

        $daObservation
            ->setObservation($text)
            ->setNumDa($numDa)
            ->setUtilisateur($this->userDataService->getUserName())
        ;

        if ($files) {
            $fileNames = $this->daFileUploader->uploadMultipleDaFiles($files, $numDa, FileUploaderForDAService::FILE_TYPE["OBSERVATION"]);
            $daObservation->setFileNames($fileNames);
        }

        $this->em->persist($daObservation);
        $this->em->flush();

        // extrait de l'observation affiché dans le suivi des opérations
        SuiviOperationService::noter('« ' . SuiviOperationService::extrait($observation) . ' »' . ($files ? ' (' . count($files) . ' fichier(s) joint(s))' : ''));
    }

    /** Récupère tous les observations d'une DA */
    public function getObservations(string $numDa): array
    {
        return $this->daObservationRepository->findBy(['numDa' => $numDa], ['dateCreation' => 'ASC']);
    }
}
