<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Dto\admin\ApplicationProfilAgenceServiceDTO;
use App\Entity\admin\utilisateur\ApplicationProfilAgenceService;
use App\Form\admin\ApplicationProfilAgenceServiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/appProfilAgServ") */
class AppProfilAgServController extends Controller
{
    /** @Route("/", name="app_profil_ag_serv_index") */
    public function index()
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(ApplicationProfilAgenceService::class)->findAll();
        $preparedData = $this->prepareForDisplay($data);
        return $this->render('admin/appProfilAgServ/list.html.twig', [
            'data' => $preparedData,
        ]);
    }

    /** @Route("/liaison", name="app_profil_ag_serv_liaison") */
    public function new(Request $request)
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        $em = $this->getEntityManager();

        $dto = new ApplicationProfilAgenceServiceDTO();
        $form = $this->getFormFactory()->createBuilder(ApplicationProfilAgenceServiceType::class, $dto)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ap = $dto->applicationProfil;
            $as = $dto->agenceServices;

            $existingIds = array_map(
                fn($l) => $l->getAgenceService()->getId(),
                $ap->getLiaisonsAgenceService()->toArray()
            );

            // Ajout
            foreach ($as as $agServ) {
                if (!in_array($agServ->getId(), $existingIds)) {
                    $apas = new ApplicationProfilAgenceService($ap, $agServ);
                    $em->persist($apas);
                }
            }

            // Suppression
            foreach ($ap->getLiaisonsAgenceService() as $link) {
                if (!in_array($link->getAgenceService()->getId(), $existingIds)) {
                    $em->remove($link);
                }
            }

            $em->flush();
            $this->redirectToRoute("app_profil_ag_serv_index");
        }

        return $this->render('admin/appProfilAgServ/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function prepareForDisplay(array $data)
    {
        $preparedData = [];

        /** @var ApplicationProfilAgenceService $liaison */
        foreach ($data as $liaison) {
            $profil = $liaison->getApplicationProfil()->getProfil();
            $application = $liaison->getApplicationProfil()->getApplication();
            $agence = $liaison->getAgenceService()->getAgence();
            $service = $liaison->getAgenceService()->getService();

            $preparedData[] = [
                'appProfilId'    => $liaison->getApplicationProfil()->getId(),
                'reference'      => $profil->getReference(),
                'nomProfil'      => $profil->getDesignation(),
                'codeApp'        => $application->getCodeApp(),
                'nomApp'         => $application->getNom(),
                'codeAgence'     => $agence->getCodeAgence(),
                'libelleAgence'  => $agence->getLibelleAgence(),
                'codeService'    => $service->getCodeService(),
                'libelleService' => $service->getLibelleService(),
            ];
        }

        return $preparedData;
    }
}
