<?php

namespace App\Factory\admin;

use App\Dto\admin\UserDTO;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\utilisateur\User;

class UserFactory
{
    public function createFromDto(UserDTO $dto): User
    {
        $user = new User();

        $personnel = $dto->personnel;
        $matricule = $personnel->getMatricule();

        /** @var AgenceServiceIrium $agenceServiceIrium */
        $agenceServiceIrium = $personnel->getAgenceServiceIriumId();

        $user
            ->setNomUtilisateur($dto->username)
            ->setMail($dto->email)
            ->setMatricule($matricule)
            ->setPersonnels($personnel)
            ->setAgenceServiceIrium($agenceServiceIrium)
            ->setCodeSage($agenceServiceIrium->getServicesagepaie())
            ->setCodeAgenceUser($agenceServiceIrium->getAgenceips())
            ->setCodeServiceUser($agenceServiceIrium->getServiceips())
        ;


        $profils = $dto->profils;
        foreach ($profils as $profil) {
            $user->addProfil($profil);
        }

        return $user;
    }

    public function createDTOFromUser(User $user): UserDTO
    {
        $dto = new UserDTO();

        $dto->username = $user->getNomUtilisateur();
        $dto->email = $user->getMail();
        $dto->personnel = $user->getPersonnels();
        $dto->profils = $user->getProfils();

        return $dto;
    }

    public function updateFromDTO(UserDTO $dto, User $user): User
    {
        $personnel = $dto->personnel;
        $matricule = $personnel->getMatricule();

        /** @var AgenceServiceIrium $agenceServiceIrium */
        $agenceServiceIrium = $personnel->getAgenceServiceIriumId();

        $user
            ->setNomUtilisateur($dto->username)
            ->setMail($dto->email)
            ->setMatricule($matricule)
            ->setPersonnels($personnel)
            ->setAgenceServiceIrium($agenceServiceIrium)
            ->setCodeSage($agenceServiceIrium->getServicesagepaie())
            ->setCodeAgenceUser($agenceServiceIrium->getAgenceips())
            ->setCodeServiceUser($agenceServiceIrium->getServiceips())
        ;

        foreach ($user->getProfils() as $existing) {
            if (!$dto->profils->contains($existing)) {
                $user->removeProfil($existing);
            }
        }
        foreach ($dto->profils as $profil) {
            if (!$user->getProfils()->contains($profil)) {
                $user->addProfil($profil);
            }
        }

        return $user;
    }
}
