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

    protected function render(string $template, array $parameters = []): string
    {
        return $this->container->get(Environment::class)->render($template, $parameters);
    }

    protected function redirectToRoute(string $route, array $parameters = []): RedirectResponse
    {
        $urlGenerator = $this->container->get(UrlGeneratorInterface::class);
        $url = $urlGenerator->generate($route, $parameters);

        return new RedirectResponse($url);
    }

    protected function getSession(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }


    protected function getUserLogger(): UserActivityLoggerService
    {
        return $this->container->get(UserActivityLoggerService::class);
    }

    public function getCurrentUser(): ?User
    {
        return $this->getEntityManager()->getRepository(User::class)->find(
            $this->getSession()->get('user_id')
        );
    }


    public function verifierSessionUtilisateur(): ?RedirectResponse
    {
        $session = $this->container->get(SessionInterface::class);

        if (!$session->has('user_id')) {
            return $this->redirectToRoute("security_login_form");
        }

        return null;
    }
   

    protected function getSessionService(): SessionService
    {
        return $this->container->get(SessionService::class);
    }
}



