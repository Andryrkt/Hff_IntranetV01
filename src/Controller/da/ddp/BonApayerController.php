<?php

namespace App\Controller\da\ddp;

use App\Controller\Controller;
use App\Entity\da\DaSoumissionFacBl;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class BonApayerController extends Controller
{
    /**
     * @Route("/da-bon-a-payer", name="da_bon_a_payer" )
     */
    public function index()
    {
        $this->verifierSessionUtilisateur();

        $daSoumissionFacBl = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class)->findAll();
        return $this->render('da/ddp/bon_a_payer.html.twig', [
            'daSoumissionFacBl' => $daSoumissionFacBl
        ]);
    }
}
