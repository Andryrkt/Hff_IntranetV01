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

    /**
     * Récupère l'identifiant de l'agence associée à l'utilisateur
     *
     * @param User $user L'utilisateur dont on veut obtenir l'ID de l'agence
     */
    public function getAgenceId(User $user)
    {
        $codeAgence = $user->getCodeAgenceUser();

        if (!$codeAgence) return null;

        $agence = $this->em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
        return $agence ? $agence->getId() : null;
    }

    /**
     * Récupère l'identifiant du service associé à l'utilisateur
     *
     * @param User $user L'utilisateur dont on veut obtenir l'ID du service
     */
    public function getServiceId(User $user)
    {
        $codeService = $user->getCodeServiceUser();

        if (!$codeService) return null;

        $service = $this->em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
        return $service ? $service->getId() : null;
    }
}
