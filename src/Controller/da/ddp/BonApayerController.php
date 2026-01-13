<?php

namespace App\Controller\da\ddp;

use App\Controller\Controller;
use App\Entity\da\DaSoumissionFacBl;
use App\Form\da\ddp\BonApayerType;
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
        $form = $this->getFormFactory()->createBuilder(BonApayerType::class)->getForm();

        // Traitement du formulaire de recherche
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dd($data);
        }

        // Récupération des données à afficher
        $daSoumissionFacBl = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class)->findAll();


        return $this->render('da/ddp/bon_a_payer.html.twig', [
            'daSoumissionFacBl' => $daSoumissionFacBl,
            'form'              => $form->createView(),
        ]);
    }
}
