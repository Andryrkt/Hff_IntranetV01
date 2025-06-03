<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DaSoumissionBc;
use App\Entity\dom\Dom;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\soumissionBC\DaSoumissionBcType;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionBcController extends Controller
{
    private  DaSoumissionBc $daSoumissionBc;
    public function __construct()
    {
        parent::__construct();

        $this->daSoumissionBc = new DaSoumissionBc();
    }

    /**
     * @Route("/soumission-bc/{numCde}", name="da_soumission_bc")
     */
    public function index(string $numCde)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->daSoumissionBc->setNumeroCde($numCde);

        $form = self::$validator->createBuilder(DaSoumissionBcType::class, $this->daSoumissionBc, [
            'method' => 'POST',
        ])->getForm();

        self::$twig->display('da/soumissionBc.html.twig', [
            'form' => $form->createView(),
            'numCde' => $numCde,
        ]);
    }
}
