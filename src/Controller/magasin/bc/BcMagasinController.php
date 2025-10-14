<?php

namespace App\Controller\magasin\bc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Form\magasin\bc\BcMagasinType;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class BcMagasinController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/soumission-bc-magasin/{numeroDevis}", name="bc_magasin_soumission", defaults={"numeroDevis"=null})
     */
    public function index(?string $numeroDevis = null): Response
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        $bcMagasin = new BcMagasin();
        $bcMagasin->setNumeroDevis($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcMagasinType::class, $bcMagasin)->getForm();

        //affichage du formulaire
        return $this->render('magasin/bc/soumission.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
