<?php

namespace App\Service\da;

use App\Entity\da\DaObservation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DaObservationRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DaObservationService
{
    private EntityManagerInterface $em;
    private DaObservationRepository $daObservationRepository;
    private FileUploaderForDAService $daFileUploader;

    public function __construct(EntityManagerInterface $em, FileUploaderForDAService $daFileUploader)
    {
        $this->em                      = $em;
        $this->daFileUploader          = $daFileUploader;
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
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

    /** Récupère tous les observations d'une DA */
    public function getObservations(string $numDa): array
    {
        return $this->daObservationRepository->findBy(['numDa' => $numDa], ['dateCreation' => 'ASC']);
    }
}
