<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DaPicking;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

class DaPickingController extends BaseController
{
    private DaPicking $daPicking;

    public function __construct()
    {
        parent::__construct();

        $this->daPicking = new DaPicking();
    }

    /**
     * @Route("/da/picking", name="da_picking")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(DaPicking::class, null)->getForm();

        return $this->render('da/picking.html.twig', []);
    }
}
