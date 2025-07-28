<?php

use App\Service\SessionManagerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->register('session_storage', NativeSessionStorage::class);

    $containerBuilder->register('http_foundation.session', Session::class)
        ->setArguments([
            new Reference('session_storage'),
        ])
        ->setPublic(true);
    $containerBuilder->setAlias(SessionInterface::class, 'http_foundation.session')
    ->setPublic(true);

    $containerBuilder->register('app.session_manager', SessionManagerService::class)
        ->setArguments([
            new Reference('http_foundation.session'),
        ])
        ->setPublic(true);
};
