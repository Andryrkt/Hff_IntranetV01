<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Form\admin\PermissionsType;
use App\Entity\admin\ApplicationProfil;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\admin\PermissionsFactory;
use App\Form\admin\ApplicationProfilPagetype;
use App\Service\Admin\PermissionsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/permission") */
class PermissionController extends Controller
{
    private PermissionsFactory $permissionsFactory;
    private PermissionsService $permissionsService;

    public function __construct(EntityManagerInterface $entityManager, PermissionsService $permissionsService, PermissionsFactory $permissionsFactory)
    {
        $this->entityManager = $entityManager;
        $this->permissionsService = $permissionsService;
        $this->permissionsFactory = $permissionsFactory;
    }

    /** @Route("", name="permission_index") */
    public function index()
    {
        $allAppProfil = $this->entityManager->getRepository(ApplicationProfil::class)->findAll();
        $preparedData = $this->prepareForDisplay($allAppProfil);
        return $this->render('admin/permissions/list.html.twig', [
            'data' => $preparedData,
        ]);
    }

    /** @Route("/{id}", name="permission_handle") */
    public function handlePermission(int $id, Request $request)
    {
        $appProfil = $this->entityManager->getRepository(ApplicationProfil::class)->find($id);
        $oldLinksAgServ = $appProfil->getLiaisonsAgenceService(); // collection de liaison (objet ApplicationProfilAgenceService)
        $oldLinksPage = $appProfil->getLiaisonsPage(); // collection de liaison (objet ApplicationProfilPage)

        $dto = $this->permissionsFactory->createDTOFromAppProfil($appProfil, $oldLinksAgServ, $oldLinksPage);
        $form = $this->getFormFactory()->createBuilder(PermissionsType::class, $dto)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->permissionsService->synchroniserLiaisons($dto, $oldLinksAgServ, $oldLinksPage);

            $this->entityManager->flush();
            $this->redirectToRoute("permission_index");
        }

        return $this->render('admin/permissions/new.html.twig', [
            'reference'  => $dto->applicationProfil->getProfil()->getReference(),
            'nomProfil'  => $dto->applicationProfil->getProfil()->getDesignation(),
            'codeApp'    => $dto->applicationProfil->getApplication()->getCodeApp(),
            'nomApp'     => $dto->applicationProfil->getApplication()->getNom(),
            'colonnes'   => ApplicationProfilPagetype::permissionsDisponibles(),
            'pagesVide'  => $dto->lignes->isEmpty(),
            'urlAppEdit' => $this->getUrlGenerator()->generate('application_update', ['id' => $dto->applicationProfil->getApplication()->getId()]),
            'form'       => $form->createView(),
        ]);
    }

    private function prepareForDisplay(array $allAppProfil)
    {
        $preparedData = [];

        /** @var ApplicationProfil $appProfil */
        foreach ($allAppProfil as $appProfil) {
            $baseData = [
                'urlPermission'  => $this->getUrlGenerator()->generate('permission_handle', ['id' => $appProfil->getId()]),
                'appProfilId'    => $appProfil->getId(),
                'reference'      => $appProfil->getProfil()->getReference(),
                'nomProfil'      => $appProfil->getProfil()->getDesignation(),
                'codeApp'        => $appProfil->getApplication()->getCodeApp(),
                'nomApp'         => $appProfil->getApplication()->getNom(),
            ];

            $liaisons = $appProfil->getLiaisonsAgenceService();

            if ($liaisons->isEmpty()) {
                $preparedData[] = $baseData + ['codeAgence' => '-', 'libelleAgence' => '-', 'codeService' => '-', 'libelleService' => '-'];
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
