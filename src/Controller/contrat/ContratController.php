<?php

namespace App\Controller\contrat;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/documentation")
 */
class ContratController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/nouveau-contrat", name="new_contrat")
     */
    public function nouveauContrat()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin());

        return $this->render('contrat/contrat.html.twig');
    }
}
