<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Controller\Traits\da\DemandeApproTrait;
use App\Entity\admin\Application;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproFormType;
use App\Form\da\DemandeApproLRCollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaNewController extends Controller
{
    use DemandeApproTrait;

    /**
     * @Route("/first-form", name="da_first_form")
     */
    public function firstForm()
    {
        self::$twig->display('da/first-form.html.twig');
    }

    /**
     * @Route("/new/{id}", name="da_new")
     */
    public function new($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // obtenir le dit correspondant à l'id {id} du DIT
        /** 
         * @var DemandeIntervention $dit DIT correspondant à l'id $id
         */
        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);

        // $dit->getAgenceDebiteurId();

        $demandeAppro = new DemandeAppro;
        $this->initialisationDemandeAppro($demandeAppro, $dit);

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro
                ->setDemandeur($this->getUser()->getNomUtilisateur())
                ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ;
            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                $DAL
                    ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal('Ouvert')
                ;
                self::$em->persist($DAL);
            }

            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DAP']);
            $application->setDerniereId($demandeAppro->getNumeroDemandeAppro());

            self::$em->persist($application);
            self::$em->persist($demandeAppro);

            self::$em->flush();

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("da_list");
        }

        self::$twig->display('da/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
