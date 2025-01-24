<?php

namespace App\Controller\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\tik\DemandeSupportInformatiqueType;


class ModificationTikController extends Controller
{
    /**
     * @Route("/tik-modification-edit/{id}", name="tik_modification_edit")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        $supportInfo = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);

        //agence et service
        $agenceRepository = self::$em->getRepository(Agence::class);
        $serviceRepository = self::$em->getRepository(Service::class);
        $agenceEmetteur = $agenceRepository->find($supportInfo->getAgenceEmetteurId())->getCodeAgence() . ' ' . $agenceRepository->find($supportInfo->getAgenceEmetteurId())->getLibelleAgence();
        $serviceEmetteur = $serviceRepository->find($supportInfo->getServiceEmetteurId())->getCodeService() . ' ' . $serviceRepository->find($supportInfo->getServiceEmetteurId())->getLibelleService();
        $supportInfo->setAgenceEmetteur($agenceEmetteur);
        $supportInfo->setServiceEmetteur($serviceEmetteur);
        $supportInfo->setAgence($agenceRepository->find($supportInfo->getAgenceDebiteurId()));
        $supportInfo->setService($serviceRepository->find($supportInfo->getServiceDebiteurId()));

        //fichier
        $fichiers = $supportInfo->getFileNames();

        //formulaire
        $form = self::$validator->createBuilder(DemandeSupportInformatiqueType::class, $supportInfo)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //envoi les donnée dans la base de donnée
            self::$em->persist($supportInfo);
            self::$em->flush();

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("liste_tik_index");
        }

        $this->logUserVisit('tik_modification_edit', [
            'id' => $id
        ]); // historisation du page visité par l'utilisateur 

        self::$twig->display('tik/demandeSupportInformatique/edit.html.twig', [
            'fichiers' => $fichiers,
            'form' => $form->createView()
        ]);
    }
}
