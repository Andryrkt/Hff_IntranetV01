<?php

namespace App\Factory;

use App\Controller\Authentification;
use App\Model\LdapModel;
use App\Service\da\EmailDaService;
use App\Service\da\FileUploaderForDAService;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;

class AuthentificationControllerFactory
{
    private $ldapModel;
    private $emailDaService;
    private $daFileUploader;
    private $daAfficherRepository;
    private $demandeApproRepository;
    private $demandeApproLRepository;
    private $demandeApproLRRepository;

    public function __construct(
        LdapModel $ldapModel,
        EmailDaService $emailDaService,
        FileUploaderForDAService $daFileUploader,
        ?DaAfficherRepository $daAfficherRepository = null,
        ?DemandeApproRepository $demandeApproRepository = null,
        ?DemandeApproLRepository $demandeApproLRepository = null,
        ?DemandeApproLRRepository $demandeApproLRRepository = null
    ) {
        $this->ldapModel = $ldapModel;
        $this->emailDaService = $emailDaService;
        $this->daFileUploader = $daFileUploader;
        $this->daAfficherRepository = $daAfficherRepository;
        $this->demandeApproRepository = $demandeApproRepository;
        $this->demandeApproLRepository = $demandeApproLRepository;
        $this->demandeApproLRRepository = $demandeApproLRRepository;
    }

    public function create(): Authentification
    {
        // Résoudre manuellement le chemin du fichier CSV
        $basePathLong = $_ENV['BASE_PATH_LONG'] ?? 'C:/wamp64/www/Hffintranet';
        $authCsvFilePath = $basePathLong . '/src/Controller/authentification.csv';

        return new Authentification(
            $this->ldapModel,
            $authCsvFilePath,
            $this->emailDaService,
            $this->daFileUploader,
            $this->daAfficherRepository,
            $this->demandeApproRepository,
            $this->demandeApproLRepository,
            $this->demandeApproLRRepository
        );
    }
}
