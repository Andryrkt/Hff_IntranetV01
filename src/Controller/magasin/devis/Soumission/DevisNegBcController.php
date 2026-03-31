<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use App\Factory\magasin\devis\Soumission\BcFactory;
use App\Form\magasin\devis\Soumission\BcType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegBcController extends Controller
{

    /**
     * @Route("/soumission-bc-neg/{numeroDevis}", name="bc_neg_soumission", defaults={"numeroDevis"=null})
     */
    public function index($numeroDevis)
    {
        $codeSociette = $this->getSecurityService()->getCodeSocieteUser();

        $factory = new BcFactory();
        $bcDto = $factory->create($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcType::class, $bcDto, [
            'method' => 'POST',
        ])->getForm();

        //affichage du formulaire
        return $this->render('magasin/devis/soumission/bc.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
