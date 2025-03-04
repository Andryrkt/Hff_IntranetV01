<?php

use Symfony\Component\DependencyInjection\Reference;
use App\Service\log\UserActivityLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->register(UserActivityLoggerService::class, UserActivityLoggerService::class)
        ->setArguments([
            new Reference(SessionInterface::class),
            new Reference(EntityManagerInterface::class)
        ])
        ->setAutowired(true)
        ->setAutoconfigured(true)
        ->setPublic(true);
};
