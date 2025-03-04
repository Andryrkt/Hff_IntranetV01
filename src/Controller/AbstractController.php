<?php
namespace App\Controller;

use Twig\Environment;
use App\Entity\admin\utilisateur\User;
use App\Service\session\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\log\UserActivityLoggerService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractController
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    

    /**
     * methode pour afficher une vue twig
     *
     * @param string $template
     * @param array $parameters
     * @return string
     */
    protected function render(string $template, array $parameters = []): string
    {
        return $this->container->get(Environment::class)->render($template, $parameters);
    }

    /**
     * methode pour rediriger vers une route
     *
     * @param string $route (nom de la route)
     * @param array $parameters (variable à utiliser dans la page destinataire)
     * @return RedirectResponse
     */
    protected function redirectToRoute(string $route, array $parameters = []): RedirectResponse
    {
        $urlGenerator = $this->container->get(UrlGeneratorInterface::class);
        $url = $urlGenerator->generate($route, $parameters);

        return new RedirectResponse($url);
    }

    /**
     * Methode qui recupere la session
     *
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }

    /**
     * Methode qui recupere l'entity manager
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    /**
     * Methode qui recupere le service de log de page (enregistrer dans le base de données)
     *
     * @return UserActivityLoggerService
     */
    protected function getUserLogger(): UserActivityLoggerService
    {
        return $this->container->get(UserActivityLoggerService::class);
    }

    /**
     * Recuperation les information de l'utilisateur connecté
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        return $this->getEntityManager()->getRepository(User::class)->find(
            $this->getSession()->get('user_id')
        );
    }


    /**
     * Verification si l'utilisateur est connecter
     *
     * @return RedirectResponse|null
     */
    public function verifierSessionUtilisateur(): ?RedirectResponse
    {
        $session = $this->container->get(SessionInterface::class);

        if (!$session->has('user_id')) {
            return $this->redirectToRoute("security_login_form");
        }

        return null;
    }

    /**
     * recupère le service de session
     *
     * @return SessionService
     */
    protected function getSessionService(): SessionService
    {
        return $this->container->get(SessionService::class);
    }

    /**
     * recupère l'url de base
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return $this->container->getParameter('base_url');
    }

}



