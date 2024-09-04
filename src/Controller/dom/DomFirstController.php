<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm1Type;
use App\Entity\admin\dom\SousTypeDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomFirstController extends Controller
{

    /**
     * @Route("/dom-first-form", name="dom_first_form")
     */
    public function firstForm(Request $request)
    {
        $dom = new Dom();

        //INITIALISATION 
        $agenceServiceIps= $this->agenceServiceIpsString();
        $dom
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'] )
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument(self::$em->getRepository(SousTypeDocument::class)->find(2))
            ->setSalarier('PERMANENT')
        ;

        $form =self::$validator->createBuilder(DomForm1Type::class, $dom)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
          
            $dom->setSalarier($form->get('salarie')->getData());
            $formData = $form->getData()->toArray();

            $this->sessionService->set('form1Data', $formData);

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }
        
        self::$twig->display('doms/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}