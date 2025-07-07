<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DaPicking;
use Symfony\Component\Routing\Annotation\Route;

class DaPickingController extends Controller
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

        $form = self::$validator->createBuilder(DaPicking::class, null)->getForm();

        self::$twig->display('da/picking.html.twig', []);
    }
}
