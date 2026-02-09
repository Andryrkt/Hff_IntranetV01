<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Controller\Traits\da\MarkupIconTrait;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/demande-appro") */
class DaNewController extends Controller
{
    use MarkupIconTrait;

    /**
     * @Route("/da-first-form", name="da_first_form")
     */
    public function firstForm()
    {
        // Vérification si user connecté
        $this->verifierSessionUtilisateur();

        $estAtelier = $this->estUserDansServiceAtelier();
        $estCreateurDeDADirecte = $this->estCreateurDeDADirecte();
        $estAdmin = $this->estAdmin();

        // Préparer les options disponibles
        $options = [];

        if ($estAdmin || $estAtelier) {
            $options['avecDit'] = [
                'label' => 'Demande d’approvisionnement avec DIT',
                'url'   => $this->getUrlGenerator()->generate('da_list_dit'),
                'icon'  => $this->getIconDaAvecDIT(),
                'type'  => 'simple'
            ];
        }

        if ($estAdmin || $estCreateurDeDADirecte) {
            $options['direct'] = [
                'label' => 'Demande d’achat',
                'url'   => $this->getUrlGenerator()->generate('da_new_achat', ['id' => 0]),
                'icon'  => $this->getIconDaDirect(),
                'type'  => 'simple'
            ];

            $options['reappro'] = [
                'label' => 'Demande de réapprovisionnement mensuel',
                'url'   => $this->getUrlGenerator()->generate('da_new_reappro_mensuel', ['id' => 0]),
                'icon'  => $this->getIconDaReapproMensuel(),
                'type'  => 'simple'
            ];
        }

        return $this->render('da/first-form.html.twig', [
            'options' => $options
        ]);
    }
}
