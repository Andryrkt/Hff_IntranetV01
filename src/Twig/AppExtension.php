<?php

namespace App\Twig;

use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use App\Entity\admin\utilisateur\User;
use Twig\TwigFunction;
use Psr\Container\ContainerInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getGlobals(): array
    {
        // Récupération des services via le conteneur injecté
        $session = $this->container->get('session');
        $requestStack = $this->container->get('request_stack');
        $tokenStorage = $this->container->get('security.token_storage');
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $user = null;
        $token = $tokenStorage->getToken();

        $notification = $session->get('notification');
        $session->remove('notification'); // Supprime la notification après l'affichage

        if ($session->get('user_id') !== null) {
            $user = $em->getRepository(User::class)->find($session->get('user_id'));
        }

        return [
            'App' => [
                'user' => $user,
                'base_path' => $_ENV['BASE_PATH_COURT'],
                'base_path_long' => $_ENV['BASE_PATH_FICHIER'],
                'base_path_fichier' => $_ENV['BASE_PATH_FICHIER_COURT'],
                'session' => $session,
                'request' => $requestStack->getCurrentRequest(),
                'notification' => $notification,
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
        // Récupération du service DomModel via le conteneur injecté
        $domModel = $this->container->get('App\Model\dom\DomModel');

        return $domModel->verifierSiTropPercu($numeroDom);
    }
}
