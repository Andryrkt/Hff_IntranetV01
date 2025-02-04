<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

abstract class AbstractController
{
    protected ContainerInterface $container;
    protected Environment $twig;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig'); // Récupère Twig automatiquement
    }

    protected function render(string $template, array $parameters = []): void
    {
        echo $this->twig->render($template, $parameters);
    }
}
