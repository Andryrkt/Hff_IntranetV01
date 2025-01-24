<?php

namespace App\Service;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;

use Symfony\Component\HttpFoundation\Request;

class AccessControlService 
{

    private $em;
    private $sessionService;
   private $user;
   private $generator;
   private $request;

    public function __construct()
    {
        $this->em = Controller::getEntity();
        $this->generator = Controller::getGenerator();
        $this->sessionService = new SessionManagerService();
        $userId = $this->sessionService->get('user_id');

        $this->user = $this->em->getRepository(User::class)->find(15);
        $this->request = new Request();
    
    }
    
    public function hasAccessApp(string $application): bool
    {
        $apps = [];
        foreach ($this->user->getApplications() as  $app) {
            $apps[] = $app->getCodeApp();
        }
        
        return in_array($application, $apps);
    }

    public function hasAccessRole(string $role): bool
    {
        $roles = [];
        foreach($this->user->getRoles() as $role) {
            $roles[] = $role->getRoleName();
        }   
        
        return in_array($role, $roles);
    }

    public function hasAccessSociette(string $societte): bool
    {
        $societtes = [];
        foreach ($this->user->getSociettes() as $societte) {
          $societtes[] = $societte->getCodeSociete();
        }
        return in_array($societte, $societtes);
    }

    private function hasAccessAgenceServ(string $agServEmetteur): bool
    {
        $agenceServices = [];
        foreach ($this->user->getServices() as  $service) {
           $agenceServices[] = $this->user->getAgences()->getCodeAgence() . '-' . $service->getCodeService();
        }

        return in_array($agServEmetteur, $agenceServices);
    }

    
}