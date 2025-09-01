<?php

namespace App\Controller;

use App\Service\navigation\MenuService;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
class HomeController extends BaseController
{
    private $menuService;

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
        \App\Service\ExcelService $excelService
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
    }


    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->logUserVisit('profil_acceuil'); // historisation du page visité par l'utilisateur

        $menuItems = $this->menuService->getMenuStructure();

        $this->getTwig()->display(
            'main/accueil.html.twig',
            [
                'menuItems' => $menuItems,
            ]
        );
    }
}
