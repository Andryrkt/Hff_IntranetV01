<?php

namespace App\Controller\da\ddp;

use App\Controller\Controller;
use App\Form\da\ddp\BonApayerType;
use App\Entity\ddp\DemandePaiement;
use App\Service\da\FileCheckerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class BonApayerController extends Controller
{
    /**
     * @Route("/consultation-facture", name="da_bon_a_payer" )
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();

        // Création du formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(BonApayerType::class, null, ['method' => 'GET'])->getForm();

        // Traitement du formulaire de recherche
        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        // Récupération des données dans la table demande_paiement
        $ddp = $this->getEntityManager()->getRepository(DemandePaiement::class)->findByCriteria($criteria);
        // TODO: transformation en DTO (DemandePaiementDto)

        // chemin fichier BAP
        $fileCheckerService = new FileCheckerService($_ENV['BASE_PATH_FICHIER']);

        return $this->render('da/ddp/bon_a_payer.html.twig', [
            'ddp'               => $ddp,
            'form'              => $form->createView(),
            'fileCheckerService' => $fileCheckerService,
        ]);
    }
}
