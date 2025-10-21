<?php

namespace App\Api\admin;

use App\Controller\Controller;
use App\Entity\admin\Personnel;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Annotation\Route;

class ContactAgenceAteApi extends Controller
{

    /**
     * @Route("/api/contact-agence-ate/{id}", name="api_contact_agence_ate_matricule")
     *
     * @param string $matricule
     * @return void
     */
    public function getMatriculeData($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $id]);

        $nomEmail = [
            'id' => $user->getId(),
            'prenom' => $user->getPersonnels()->getPrenoms(),
            'telephone' => $user->getNumTel()
        ];

        header("Content-type:application/json");

        echo json_encode($nomEmail);
    }
}
