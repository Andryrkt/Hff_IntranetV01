<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Dto\admin\ApplicationProfilAgenceServiceDTO;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\utilisateur\ApplicationProfilAgenceService;
use App\Form\admin\ApplicationProfilAgenceServiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/droits") */
class AppProfilAgServController extends Controller
{
    /** @Route("", name="app_profil_ag_serv_index") */
    public function index()
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        $allAppProfil = $this->getEntityManager()->getRepository(ApplicationProfil::class)->findAll();
        $preparedData = $this->prepareForDisplay($allAppProfil);
        return $this->render('admin/appProfilAgServ/list.html.twig', [
            'data' => $preparedData,
        ]);
    }

    /** @Route("/liaison/{id}", name="app_profil_ag_serv_liaison") */
    public function liaison(int $id, Request $request)
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        $em = $this->getEntityManager();

        $dto = new ApplicationProfilAgenceServiceDTO();
        $dto->applicationProfil = $em->getRepository(ApplicationProfil::class)->find($id);
        /** Obtenir les agences services deja liées au combinaison ApplicationProfil */
        $oldLinks = $dto->applicationProfil->getLiaisonsAgenceService(); // collection de liaison (objet ApplicationProfilAgenceService)
        $dto->agenceServices = $oldLinks->map(fn($l) => $l->getAgenceService())->toArray(); // tableau d'objets AgenceService
        $form = $this->getFormFactory()->createBuilder(ApplicationProfilAgenceServiceType::class, $dto)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ap = $dto->applicationProfil;
            $as = $dto->agenceServices; // nouveau tableau d'objets AgenceService (selectionné dans le formulaire)

            $existingIds = array_map(
                fn($l) => $l->getAgenceService()->getId(),
                $oldLinks->toArray()
            ); // tableau d'ids des agences services deja liées

            // Ajout
            foreach ($as as $agServ) {
                if ($existingIds && !in_array($agServ->getId(), $existingIds)) {
                    $apas = new ApplicationProfilAgenceService($ap, $agServ);
                    $em->persist($apas);
                }
            }

            // Suppression
            if (!$oldLinks->isEmpty()) {
                foreach ($oldLinks as $link) {
                    if (!in_array($link->getAgenceService()->getId(), $existingIds)) {
                        $em->remove($link);
                    }
                }
            }

            $em->flush();
            $this->redirectToRoute("app_profil_ag_serv_index");
        }

        return $this->render('admin/appProfilAgServ/new.html.twig', [
            'reference' => $dto->applicationProfil->getProfil()->getReference(),
            'nomProfil' => $dto->applicationProfil->getProfil()->getDesignation(),
            'codeApp'   => $dto->applicationProfil->getApplication()->getCodeApp(),
            'nomApp'    => $dto->applicationProfil->getApplication()->getNom(),
            'form'      => $form->createView(),
        ]);
    }

    private function prepareForDisplay(array $allAppProfil)
    {
        $preparedData = [];

        /** @var ApplicationProfil $appProfil */
        foreach ($allAppProfil as $appProfil) {
            $baseData = [
                'urlLiaison'     => $this->getUrlGenerator()->generate('app_profil_ag_serv_liaison', ['id' => $appProfil->getId()]),
                'appProfilId'    => $appProfil->getId(),
                'reference'      => $appProfil->getProfil()->getReference(),
                'nomProfil'      => $appProfil->getProfil()->getDesignation(),
                'codeApp'        => $appProfil->getApplication()->getCodeApp(),
                'nomApp'         => $appProfil->getApplication()->getNom(),
            ];

            $liaisons = $appProfil->getLiaisonsAgenceService();

            if ($liaisons->isEmpty()) {
                $preparedData[] = $baseData + ['liaisons' => ''];
                continue;
            }

            foreach ($liaisons as $liaison) {
                $agence = $liaison->getAgenceService()->getAgence();
                $service = $liaison->getAgenceService()->getService();
                $preparedData[] = $baseData + [
                    'codeAgence'     => $agence->getCodeAgence(),
                    'libelleAgence'  => $agence->getLibelleAgence(),
                    'codeService'    => $service->getCodeService(),
                    'libelleService' => $service->getLibelleService(),
                ];
            }
        }

        usort($preparedData, static function (array $a, array $b) {
            return ($a['appProfilId'] <=> $b['appProfilId'])
                ?: ($a['codeApp'] <=> $b['codeApp'])
                ?: (($a['codeAgence'] ?? '') <=> ($b['codeAgence'] ?? ''))
                ?: (($a['codeService'] ?? '') <=> ($b['codeService'] ?? ''));
        });

        return $preparedData;
    }
}
