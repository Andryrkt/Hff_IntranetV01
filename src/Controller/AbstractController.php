<?php

namespace App\Controller;

use Twig\Environment;

abstract class AbstractController
{
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    protected function render(string $template, array $parameters = []): string
    {
        return $this->twig->render($template, $parameters);
    }
}

