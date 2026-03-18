<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use App\Model\magasin\devis\DevisNegModel;
use Symfony\Component\Routing\Annotation\Route;


class ListeDevisNegController extends Controller
{
    use AutorisationTrait;

    private DevisNegModel $listeDevisMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new DevisNegModel();
    }

    /**
     * @Route("/liste-devis-neg", name="liste_devis_neg")
     *
     * @return void
     */
    public function listeDevisNeg()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        $devisNeg = $this->listeDevisMagasinModel->getDevisNeg();
        dd($devisNeg);
        return $this->render('magasin/devis/liste_devis_neg.html.twig', [
            'devisNeg' => $devisNeg
        ]);
    }
}
