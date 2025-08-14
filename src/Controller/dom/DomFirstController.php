<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Form\dom\DomForm1Type;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\Service;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dom = new Dom();

        $userId = $this->sessionService->get('user_id', []);
        $user = self::$em->getRepository(User::class)->find($userId);
        $agenceAutoriserId = $user->getAgenceAutoriserIds();
        $codeAgences = [];
        foreach ($agenceAutoriserId as $value) {
            $codeAgences[] = self::$em->getRepository(Agence::class)->find($value)->getCodeAgence();
        }

        $serviceAutoriserId = $user->getServiceAutoriserIds();
        $codeService = [];
        foreach ($serviceAutoriserId as $value) {
            $codeService[] = self::$em->getRepository(Service::class)->find($value)->getCodeService();
        }

        //INITIALISATION 
        $agenceServiceIps = $this->agenceServiceIpsString();
        $dom
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument(self::$em->getRepository(SousTypeDocument::class)->find(2))
            ->setSalarier('PERMANENT')
            ->setCodeAgenceAutoriser($codeAgences)
            ->setCodeServiceAutoriser($codeService)
        ;


        $form = self::$validator->createBuilder(DomForm1Type::class, $dom)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $salarier = $form->get('salarie')->getData();

            $dom->setSalarier($salarier);
            $formData = $form->getData()->toArray();


            $this->sessionService->set('form1Data', $formData);

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }

        $this->logUserVisit('dom_first_form'); // historisation du page visité par l'utilisateur

        self::$twig->display('doms/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function autorisationRole($em): bool
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->sessionService->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds) || in_array(4, $roleIds);
    }

    private function notification($message)
    {
        $this->sessionService->set('notification', ['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dom_first_form");
    }
}
