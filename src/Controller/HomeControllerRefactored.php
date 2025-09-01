<?php

namespace App\Controller;

use App\Service\navigation\MenuService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur de la page d'accueil refactorisé pour utiliser l'injection de dépendances
 */
class HomeControllerRefactored extends BaseController
{
    private MenuService $menuService;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator,
        \Twig\Environment $twig,
        \Symfony\Component\Form\FormFactoryInterface $formFactory,
        \Symfony\Component\HttpFoundation\Session\SessionInterface $session,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage,
        \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker,
        \App\Service\FusionPdf $fusionPdf,
        \App\Model\LdapModel $ldapModel,
        \App\Model\ProfilModel $profilModel,
        \App\Model\badm\BadmModel $badmModel,
        \App\Model\admin\personnel\PersonnelModel $personnelModel,
        \App\Model\dom\DomModel $domModel,
        \App\Model\da\DaModel $daModel,
        \App\Model\dom\DomDetailModel $domDetailModel,
        \App\Model\dom\DomDuplicationModel $domDuplicationModel,
        \App\Model\dom\DomListModel $domListModel,
        \App\Model\dit\DitModel $ditModel,
        \App\Model\TransferDonnerModel $transferDonnerModel,
        \App\Service\SessionManagerService $sessionManagerService,
        \App\Service\ExcelService $excelService,
        MenuService $menuService
    ) {
        parent::__construct(
            $entityManager,
            $urlGenerator,
            $twig,
            $formFactory,
            $session,
            $tokenStorage,
            $authorizationChecker,
            $fusionPdf,
            $ldapModel,
            $profilModel,
            $badmModel,
            $personnelModel,
            $domModel,
            $daModel,
            $domDetailModel,
            $domDuplicationModel,
            $domListModel,
            $ditModel,
            $transferDonnerModel,
            $sessionManagerService,
            $excelService
        );

        $this->menuService = $menuService;
    }

    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        // Vérification si user connecté
        $this->verifierSessionUtilisateur();

        // Historisation de la page visitée par l'utilisateur
        $this->logUserVisit('profil_acceuil');

        $menuItems = $this->menuService->getMenuStructure();

        return $this->render(
            'main/accueil.html.twig',
            [
                'menuItems' => $menuItems,
            ]
        );
    }
}
