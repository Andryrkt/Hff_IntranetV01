<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Controller\Controller;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use App\Entity\admin\utilisateur\User;
use App\Model\dom\DomModel;
use App\Entity\tik\DemandeSupportInformatique;
use App\Model\dw\DossierInterventionAtelierModel;
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
    private $dwModel;
    private $authorizationChecker;


    public function __construct(SessionInterface $session, RequestStack $requestStack, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {

        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->domModel = new DomModel;
    }

    public function getGlobals(): array
    {
        $user = null;


        $notification = $this->session->get('notification');
        $this->session->remove('notification'); // Supprime la notification après l'affichage

        if ($this->session->get('user_id') !== null) {
            $user = Controller::getEntity()->getRepository(User::class)->find($this->session->get('user_id'));
        }

        return [
            'App' => [
                'user' => $user,
                'base_path' => $_ENV['BASE_PATH_COURT'],
                'base_path_long' => $_ENV['BASE_PATH_FICHIER'],
                'base_path_fichier' => $_ENV['BASE_PATH_FICHIER_COURT'],
                'session' => $this->session,
                'request' => $this->requestStack->getCurrentRequest(),
                'notification' => $notification,
            ],
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('trop_percu', [$this, 'tropPercu']),
            new TwigFunction('get_path_or_max', [$this, 'getPathOrMax']),
        ];
    }

    public function tropPercu(string $numeroDom)
    {
        return $this->domModel->verifierSiTropPercu($numeroDom);
    }
}
