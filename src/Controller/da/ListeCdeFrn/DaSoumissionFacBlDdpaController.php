<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\daCdeFrn\DaSoumissionFacBlDdpaType;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlDdpaFactory;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlDdpaController extends Controller
{
    private DaSoumissionFacBlDdpaFactory $daSoumissionFacBlDdpaFactory;
    

    public function __construct()
    {
        $this->daSoumissionFacBlDdpaFactory = new DaSoumissionFacBlDdpaFactory($this->getEntityManager());
        
    }

    /**
     * @Route("/soumission-facbl-ddpa/{numCde}", name="da_soumission_facbl_ddpa")
     */
    public function index(int $numCde)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //initialisation 
        $dto = $this->daSoumissionFacBlDdpaFactory->initialisation($numCde, $this->getUserName());

        // creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlDdpaType::class, $dto, [
            'method'  => 'POST'
        ])->getForm();

        return $this->render('da/soumissionFacBlDdpa.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
