<?php

namespace App\Factory\admin;

use App\Dto\admin\UserDTO;
use App\Entity\admin\utilisateur\User;

class UserFactory
{
    public function createFromDto(UserDTO $dto): User
    {
        $user = new User();

        $personnel = $dto->personnel;
        $matricule = $personnel->getMatricule();
        $agenceServiceIrium = $personnel->getAgenceServiceIriumId();

        $user->setNomUtilisateur($dto->username);
        $user->setMail($dto->email);
        $user->setMatricule($matricule);
        $user->setPersonnels($personnel);
        $user->setAgenceServiceIrium($agenceServiceIrium);

        $profils = $dto->profils;
        foreach ($profils as $profil) {
            $user->addProfil($profil);
        }

        return $user;
    }
}
