<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Form\dom\DomForm1Type;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\SousTypeDocument;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomFirstController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/dom-first-form", name="dom_first_form")
     */
    public function firstForm(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DOM);
        /** FIN AUtorisation acées */

        $dom = new Dom();

        $user = $this->getUser();
        $agenceAutoriserId = $user->getAgenceAutoriserIds();
        $codeAgences = [];
        foreach ($agenceAutoriserId as $value) {
            $codeAgences[] = $this->getEntityManager()->getRepository(Agence::class)->find($value)->getCodeAgence();
        }

        $serviceAutoriserId = $user->getServiceAutoriserIds();
        $codeService = [];
        foreach ($serviceAutoriserId as $value) {
            $codeService[] = $this->getEntityManager()->getRepository(Service::class)->find($value)->getCodeService();
        }

        //INITIALISATION 
        $agenceServiceIps = $this->agenceServiceIpsString();
        $dom
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument($this->getEntityManager()->getRepository(SousTypeDocument::class)->find(2))
            ->setSalarier('PERMANENT')
            ->setCodeAgenceAutoriser($codeAgences)
            ->setCodeServiceAutoriser($codeService)
        ;


        $form = $this->getFormFactory()->createBuilder(DomForm1Type::class, $dom)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $salarier = $form->get('salarie')->getData();

            $dom->setSalarier($salarier);
            $formData = $form->getData()->toArray();


            $this->getSessionService()->set('form1Data', $formData);

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }

        $this->logUserVisit('dom_first_form'); // historisation du page visité par l'utilisateur

        return $this->render('doms/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function autorisationRole($em): bool
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->getSessionService()->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds) || in_array(4, $roleIds);
    }

    private function notification($message)
    {
        $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dom_first_form");
    }
}
