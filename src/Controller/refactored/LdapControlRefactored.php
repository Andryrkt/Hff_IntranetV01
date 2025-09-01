<?php

namespace App\Controller;

use App\Model\LdapModel;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
class LdapControl
{
    private $LdapModel;

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

    public function connect_to_user($user, $pswd)
    {

        return $this->LdapModel->userConnect($user, $pswd);
    }
}
