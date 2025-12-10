<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use App\Entity\admin\utilisateur\User;
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
        $user = null;
        $token = $this->tokenStorage->getToken();

        $notification = $this->session->get('notification');
        $this->session->remove('notification'); // Supprime la notification après l'affichage

        $userInfo = $this->session->get('user_info');

        if ($userInfo) $user = $this->em->getRepository(User::class)->find($userInfo['id']);

        return [
            'App' => [
                'user'              => $user,
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
