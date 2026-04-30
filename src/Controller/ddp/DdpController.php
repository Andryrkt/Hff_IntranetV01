<?php

namespace App\Controller\ddp;


use App\Controller\Controller;
use App\Factory\ddp\DdpFactory;
use App\Form\ddp\DdpType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ddp")
 */
class DdpController extends Controller
{
    private DdpFactory $ddpFactory;

    public function __construct(DdpFactory $ddpFactory)
    {
        parent::__construct();
        $this->ddpFactory = $ddpFactory;
    }

    /**
     * @Route("/new/{type_ddp}", name="new_ddp_avance")
     */
    public function new(int $typeDdp)
    {
        $dto = $this->ddpFactory->initialisation($typeDdp);
        $form = $this->getFormFactory()->createBuilder(DdpType::class, $dto)->getForm();
        return $this->render('ddp/new.html.twig', [
            'type_ddp' => $typeDdp
        ]);
    }
}
