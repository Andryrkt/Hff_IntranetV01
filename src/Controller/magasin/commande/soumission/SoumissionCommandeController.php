<?php

namespace App\Controller\magasin\commande\soumission;

use App\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationCDEFNRService;
use App\Form\magasin\Commande\SoumissionCommande\SoumissionCommandeType;

/**
 * @Route("/magasin/commande")
 */
class SoumissionCommandeController extends Controller
{
    private HistoriqueOperationCDEFNRService $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationCDEFNRService($this->getEntityManager());
    }

    /**
     * @Route("/generer-commande-fournisseur", name="generer_commande_fournisseur")
     */
    public function soumissionCommande(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(SoumissionCommandeType::class, null, [
            'method' => 'POST',
        ])->getForm();

        $form->handleRequest($request);

        $this->logUserVisit('generer_commande_fournisseur');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->soumettreAValider($form);
        }

        return $this->render('magasin/commande/soumission/soumissionCommandeFournisseur.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function soumettreAValider(FormInterface $form) {}
}
