<?php

namespace App\Twig;

use App\Entity\admin\utilisateur\Profil;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $session;
    private $requestStack;


    public function __construct(SessionInterface $session, RequestStack $requestStack)
    {

        $this->session = $session;
        $this->requestStack = $requestStack;
    }

    public function getGlobals(): array
    {
        $notification = $this->session->get('notification');
        $this->session->remove('notification'); // Supprime la notification après l'affichage

        $userInfo = $this->session->get('user_info');

        return [
            'App' => [
                'userConnecter'     => [
                    'firstname'    => $userInfo['firstname'] ?? '',
                    'lastname'     => $userInfo['lastname'] ?? '',
                    'fullname'     => $userInfo['fullname'] ?? '',
                    'agenceIPS'    => $userInfo['default_agence_code'] ?? '',
                    'serviceIPS'   => $userInfo['default_service_code'] ?? '',
                    'isAdmin'      => ($userInfo['profil_id'] ?? 0) === Profil::HFF_ADMIN,
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
}
