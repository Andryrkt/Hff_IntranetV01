<?php

namespace App\Controller\ddp;


use App\Controller\Controller;
use App\Dto\ddp\DdpDto;
use App\Factory\ddp\DdpFactory;
use App\Form\ddp\DdpType;
use App\Service\ddp\DdpGeneratorNameService;
use App\Service\fichier\UploderFileService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function new(int $typeDdp, Request $request)
    {
        // initialisation DTO
        $dto = $this->ddpFactory->initialisation($typeDdp);
        //Creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DdpType::class, $dto)->getForm();
        // Traitement du formulaire
        $this->traitementDuFormulaire($form,  $request);
        return $this->render('ddp/magasin/new.html.twig', [
            'form' => $form->createView(),
            'type_ddp' => $typeDdp
        ]);
    }

    private function traitementDuFormulaire(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DdpDto $dto */
            $dto = $form->getData();
        }
    }
}
