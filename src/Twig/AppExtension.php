<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Controller\Controller;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $session;
    private $requestStack;
    private $tokenStorage;
    private $authorizationChecker;


    public function __construct(SessionInterface $session, RequestStack $requestStack, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {

        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getGlobals(): array
    {
        $user = null;
        $token = $this->tokenStorage->getToken();


        $notification = $this->session->get('notification');
        $this->session->remove('notification'); // Supprime la notification après l'affichage

        if ($this->session->get('user_id') !== null) {
            $user = Controller::getEntity()->getRepository(User::class)->find($this->session->get('user_id'));
            $nbrTikResolu = Controller::getEntity()->getRepository(DemandeSupportInformatique::class)->countByStatutDemande('62', $this->session->get('user_id'));
        }

        return [
            'App' => [
                'user'         => $user,
                'ticketing'    => [
                    'nbrTicketResolu' => $nbrTikResolu ?? '',
                ],
                'session'      => $this->session,
                'request'      => $this->requestStack->getCurrentRequest(),
                'notification' => $notification,
            ],
        ];
    }
}
