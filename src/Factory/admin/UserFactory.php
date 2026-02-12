<?php

namespace App\Factory\admin;

use App\Dto\admin\UserDTO;
use App\Entity\admin\utilisateur\User;

class UserFactory
{
    public function createFromDto(UserDTO $dto): User
    {
        $user = new User();
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        $user->setAgenceServiceIrium($dto->agenceServiceIrium);
        $user->setPersonnel($dto->personnel);
        $user->setProfils($dto->profils);

        return $user;
    }
}
