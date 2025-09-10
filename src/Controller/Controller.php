<?php

namespace App\Controller;

use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\da\EmailDaService;
use App\Service\da\FileUploaderForDAService;
use Parsedown;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    use Traits\da\DaTrait;

    protected $parsedown;
    public $request;
    public $response;

    public function __construct(
        EmailDaService $emailDaService,
        FileUploaderForDAService $daFileUploader,
        DaAfficherRepository $daAfficherRepository,
        DemandeApproRepository $demandeApproRepository,
        DemandeApproLRepository $demandeApproLRepository,
        DemandeApproLRRepository $demandeApproLRRepository
    ) {
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->parsedown = new Parsedown();

        // Assign services and repositories to properties from DaTrait
        $this->emailDaService = $emailDaService;
        $this->daFileUploader = $daFileUploader;
        $this->daAfficherRepository = $daAfficherRepository;
        $this->demandeApproRepository = $demandeApproRepository;
        $this->demandeApproLRepository = $demandeApproLRepository;
        $this->demandeApproLRRepository = $demandeApproLRRepository;
    }

    // ... Le reste du fichier Controller.php reste inchangé ...
    // Note: Les anciennes méthodes comme getEntityManager, getTwig etc. qui dépendent du conteneur global
    // devraient idéalement être remplacées par l'injection de dépendances directe au besoin.
}