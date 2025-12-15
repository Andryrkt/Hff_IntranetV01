<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Entity\admin\utilisateur\Role;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use App\Model\dom\DomModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $session;
    private $requestStack;
    private $tokenStorage;
    private $domModel;
    private $authorizationChecker;
    private $em;


    public function __construct(SessionInterface $session, RequestStack $requestStack, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $em)
    {

        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;
        $this->domModel = new DomModel;
    }

    public function getGlobals(): array
    {
        $token = $this->tokenStorage->getToken();

        $notification = $this->session->get('notification');
        $this->session->remove('notification'); // Supprime la notification après l'affichage

        $userInfo = $this->session->get('user_info');
        $roleIds = $userInfo['roles'] ?? [];

        return [
            'App' => [
                'userConnecter'     => [
                    'firstname'    => $userInfo['firstname'] ?? '',
                    'lastname'     => $userInfo['lastname'] ?? '',
                    'fullname'     => $userInfo['fullname'] ?? '',
                    'email'        => $userInfo['email'] ?? '',
                    'agenceIPS'    => $userInfo['default_agence_code'] ?? '',
                    'serviceIPS'   => $userInfo['default_service_code'] ?? '',
                    'isAdmin'      => in_array(Role::ROLE_ADMINISTRATEUR, $roleIds),
                    'isSuperAdmin' => in_array(Role::ROLE_SUPER_ADMINISTRATEUR, $roleIds),
                    'isAtelier'    => in_array(Role::ROLE_ATELIER, $roleIds),
                    'isDirection'  => in_array(Role::ROLE_DIRECTION, $roleIds),
                ],
                'base_path'         => $_ENV['BASE_PATH_COURT'],
                'base_path_long'    => $_ENV['BASE_PATH_FICHIER'],
                'base_path_fichier' => $_ENV['BASE_PATH_FICHIER_COURT'],
                'session'           => $this->session,
                'request'           => $this->requestStack->getCurrentRequest(),
                'notification'      => $notification,
            ],
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('trop_percu', [$this, 'tropPercu']),
        ];
    }

    public function tropPercu(string $numeroDom)
    {
        return $this->domModel->verifierSiTropPercu($numeroDom);
    }
}
