<?php


namespace App\Controller\da;

use App\Entity\da\DaPicking;
use App\Controller\Controller;
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
        $form = $this->getFormFactory()->createBuilder(DaPicking::class, null)->getForm();

        return $this->render('da/picking.html.twig', []);
    }
}
