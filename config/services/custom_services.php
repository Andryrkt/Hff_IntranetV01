<?php

use App\Service\log\UserActivityLoggerService;
use App\Service\session\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->register(UserActivityLoggerService::class, UserActivityLoggerService::class)
        ->setArguments([
            new Reference(SessionInterface::class),
            new Reference(EntityManagerInterface::class),
        ])
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setPublic(true);

    $containerBuilder->register(SessionService::class, SessionService::class)
    ->setArguments([
        new Reference('http_foundation.session'),
    ])
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(true);
};
