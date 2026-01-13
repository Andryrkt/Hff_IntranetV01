<?php

namespace App\Controller\da\ddp;

use App\Controller\Controller;
use App\Form\da\ddp\BonApayerType;
use App\Entity\da\DaSoumissionFacBl;
use Illuminate\Support\Facades\File;
use App\Service\da\FileCheckerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class BonApayerController extends Controller
{
    /**
     * @Route("/da-bon-a-payer", name="da_bon_a_payer" )
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

        // Récupération des données à afficher
        $daSoumissionFacBl = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class)->getAll($criteria);

        // chemin fichier BAP
        $fileCheckerService = new FileCheckerService($_ENV['BASE_PATH_FICHIER']);

        return $this->render('da/ddp/bon_a_payer.html.twig', [
            'daSoumissionFacBl' => $daSoumissionFacBl,
            'form'              => $form->createView(),
            'fileCheckerService' => $fileCheckerService,
        ]);
    }
}
