<?php

namespace App\Controller\dom;

use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm1Type;
use App\Entity\admin\dom\SousTypeDocument;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomFirstController extends Controller
{
    /**
     * @Route("/dom-first-form", name="dom_first_form")
     */
    public function firstForm(Request $request)
    {
        // Récupération de l'agence et du service de l'utilisateur connecté
        $agenceServiceIps = $this->agenceServiceIpsString();

        $dom = new Dom();

        //INITIALISATION 
        $dom = $this->initialisationDom($dom, $agenceServiceIps);

        //CREATION DU FORMULAIRE
        $form = $this->getFormFactory()->createBuilder(DomForm1Type::class, $dom, [
            'agenceServiceAutorisees' => $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DOM)
        ])->getForm();
        //TRAITEMENT DU FORMULAIRE
        $this->traitemementForm($form, $request, $dom);

        //HISTORISATION DE LA PAGE
        $this->logUserVisit('dom_first_form'); // historisation du page visité par l'utilisateur

        //RENDU DE LA VUE
        return $this->render('doms/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitemementForm($form, $request, $dom)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $salarier = $form->get('salarie')->getData();
            $dom->setSalarier($salarier);

            $formData = $form->getData()->toArray();

            $this->getSessionService()->set('form1Data', $formData);

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }
    }

    /**
     * Initialise le DOM
     * @param Dom $dom
     * @param array $agenceServiceIps
     * 
     * @return Dom
     */
    private function initialisationDom(Dom $dom, array $agenceServiceIps): Dom
    {
        return $dom
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument($this->getEntityManager()->getRepository(SousTypeDocument::class)->find(2))
            ->setSalarier('PERMANENT')
        ;
    }

    private function notification($message)
    {
        $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dom_first_form");
    }
}
