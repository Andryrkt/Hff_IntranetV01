<?php

namespace App\Service\Users;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\utilisateur\User;

class UserDataService
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getAgenceId(User $user)
    {
        $codeAgence = $user->getCodeAgenceUser();

        if (!$codeAgence) return null;

        $agence = $this->em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
        return $agence ? $agence->getId() : null;
    }

    public function getServiceId(User $user)
    {
        $codeService = $user->getCodeServiceUser();

        if (!$codeService) return null;

        $service = $this->em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
        return $service ? $service->getId() : null;
    }
}
