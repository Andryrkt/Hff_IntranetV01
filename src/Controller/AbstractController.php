<?php
namespace App\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
}



