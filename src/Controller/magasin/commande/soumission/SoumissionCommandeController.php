<?php

namespace App\Controller\magasin\commande\soumission;

use App\Controller\Controller;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\magasin\cdeFrn\CdeFrnSoumissionService;
use App\Form\magasin\Commande\SoumissionCommande\SoumissionCommandeType;

/**
 * @Route("/magasin/commande")
 */
class SoumissionCommandeController extends Controller
{
    private CdeFrnSoumissionService $cdeFrnSoumissionService;

    public function __construct(CdeFrnSoumissionService $cdeFrnSoumissionService)
    {
        $this->cdeFrnSoumissionService = $cdeFrnSoumissionService;
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

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->cdeFrnSoumissionService->soumettre($this->getUserMail(), $formData["numCmde"], $formData["generationToken"]);
        }

        $this->logUserVisit('generer_commande_fournisseur');

        return $this->render('magasin/commande/soumission/soumissionCommandeFournisseur.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
