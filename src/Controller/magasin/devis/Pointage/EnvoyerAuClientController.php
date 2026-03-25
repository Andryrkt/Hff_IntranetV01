<?php

namespace App\Controller\magasin\devis\Pointage;

use App\Controller\Controller;
use App\Factory\magasin\devis\Pointage\EnvoyerAuClientFactory;
use App\Form\magasin\devis\Pointage\EnvoyerAuClientType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class EnvoyerAuClientController extends Controller
{
    /**
     * @Route("/pointage/envoyer-au-client/{numeroDevis}", name="pointage_envoyer_au_client")
     */
    public function index($numeroDevis)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $dto = (new EnvoyerAuClientFactory())->create($numeroDevis);

        //formulaire de création
        $form = $this->getFormFactory()->createBuilder(EnvoyerAuClientType::class, $dto)->getForm();

        //affichage du formulaire
        return $this->render('magasin/devis/pointage/EnvoyerAuClient/envoyer_au_client.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
