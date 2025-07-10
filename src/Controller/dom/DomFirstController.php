<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Form\dom\DomForm1Type;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\Service;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\dom\SousTypeDocumentRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomFirstController extends Controller
{
    private AgenceRepository $agenceRepository;
    private ServiceRepository $serviceRepository;
    private SousTypeDocumentRepository $sousTypeDocumentRepository;

    public function __construct()
    {
        parent::__construct();

        $this->agenceRepository = self::$em->getRepository(Agence::class);
        $this->serviceRepository = self::$em->getRepository(Service::class);
        $this->sousTypeDocumentRepository = self::$em->getRepository(SousTypeDocument::class);
    }


    /**
     * @Route("/dom-first-form", name="dom_first_form")
     */
    public function firstForm(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dom = new Dom();

        //INITIALISATION 
        $this->initialisation($dom);

        $form = self::$validator->createBuilder(DomForm1Type::class, $dom)->getForm();
        $this->traitementFormulaire($form, $request, $dom);

        // historisation du page visité par l'utilisateur
        $this->logUserVisit('dom_first_form');

        self::$twig->display('doms/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function initialisation(Dom $dom)
    {
        $codeAgences = $this->getCodeAgenceAutoriser();
        $codeService = $this->getCodeServiceAutoriser();
        $agenceServiceIps = $this->agenceServiceIpsString();
        $dom
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument($this->sousTypeDocumentRepository->find(2))
            ->setSalarier('PERMANENT')
            ->setCodeAgenceAutoriser($codeAgences)
            ->setCodeServiceAutoriser($codeService)
        ;
    }

    private function getCodeServiceAutoriser(): array
    {
        $serviceAutoriserId = $this->getUser()->getServiceAutoriserIds();
        $codeServices = [];
        foreach ($serviceAutoriserId as $value) {
            $codeServices[] = $this->serviceRepository->find($value)->getCodeService();
        }

        return $codeServices;
    }

    private function getCodeAgenceAutoriser(): array
    {
        $agenceAutoriserId = $this->getUser()->getAgenceAutoriserIds();
        $codeAgences = [];
        foreach ($agenceAutoriserId as $value) {
            $codeAgences[] = $this->agenceRepository->find($value)->getCodeAgence();
        }

        return $codeAgences;
    }

    private function traitementFormulaire($form, Request $request, Dom $dom)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            //recupération des données du formulaire
            $salarier = $form->get('salarie')->getData();
            $dom->setSalarier($salarier);
            $formData = $form->getData()->toArray();

            //enregistrement des données du formulaire dans la session
            $this->sessionService->set('form1Data', $formData);

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }
    }

    private function notification($message)
    {
        $this->sessionService->set('notification', ['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dom_first_form");
    }
}
