<?php

namespace App\Api\da;

use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Form\da\daCdeFrn\DaCdeEnvoyerType;
use Symfony\Component\Routing\Annotation\Route;

class DaEnvoieCdeApi extends Controller
{
    use DaTrait;

    /**
     * @Route("/api/da-envoie-cde", name="da_envoie_cde_form", methods={"GET", "POST"})
     *
     * @return void
     */
    public function showForm()
    {
        $dateparDefaut = $this->ajoutJour(5); // Ajoute 5 jours à la date actuelle

        /** Formulaire pour confirmer l'envoie des commande au fournisseur */
        $form = self::$validator->createBuilder(DaCdeEnvoyerType::class, ['dateDefault' => $dateparDefaut])->getForm();

        self::$twig->display('da/shared/cdeFrn/_formulaireCdeEnvoyer.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
