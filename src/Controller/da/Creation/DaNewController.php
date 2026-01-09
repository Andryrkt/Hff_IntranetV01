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
                'label' => 'Demande d’achat avec DIT',
                'url' => $this->getUrlGenerator()->generate('da_list_dit'),
                'icon' => $this->getIconDaAvecDIT(),
                'type' => 'simple'
            ];
        }

        if ($estAdmin || $estCreateurDeDADirecte) {
            $options['direct'] = [
                'label' => 'Demande d’achat direct',
                'url' => $this->getUrlGenerator()->generate('da_new_direct', ['id' => 0]),
                'icon' => $this->getIconDaDirect(),
                'type' => 'simple'
            ];

            $options['reappro'] = [
                'label' => 'Demande de réapprovisionnement',
                'icon' => $this->faIconLayer('fa-boxes-stacked', null, '#0d6efd', null, '#cfe2ff'),
                'type' => 'groupe',
                'sousOptions' => [
                    'mensuel' => [
                        'label' => 'Réapprovisionnement mensuel',
                        'url' => $this->getUrlGenerator()->generate('da_new_reappro_mensuel', ['id' => 0]),
                        'icon' => $this->getIconDaReapproMensuel()
                    ],
                    'ponctuel' => [
                        'label' => 'Réapprovisionnement ponctuel',
                        'url' => $this->getUrlGenerator()->generate('da_new_reappro_ponctuel', ['id' => 0]),
                        'icon' => $this->getIconDaReapproPonctuel()
                    ]
                ]
            ];
        }

        return $this->render('da/first-form.html.twig', [
            'options' => $options
        ]);
    }
}
