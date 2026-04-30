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
     * @Route("/new/{typeDdp}", name="new_ddp")
     */
    public function new(int $typeDdp)
    {
        $dto = $this->ddpFactory->initialisation($typeDdp);
        $form = $this->getFormFactory()->createBuilder(DdpType::class, $dto)->getForm();
        return $this->render('ddp/magasin/new.html.twig', [
            'form' => $form->createView(),
            'type_ddp' => $typeDdp
        ]);
    }
}
