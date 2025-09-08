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
        $dateparDefaut = $this->ajouterJoursOuvrables(5); // Ajoute 5 jours à la date actuelle

        /** Formulaire pour confirmer l'envoie des commande au fournisseur */
        $form = $this->getFormFactory()->createBuilder(DaCdeEnvoyerType::class, ['dateDefault' => $dateparDefaut])->getForm();

        $this->getTwig()->display('da/shared/cdeFrn/_formulaireCdeEnvoyer.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
