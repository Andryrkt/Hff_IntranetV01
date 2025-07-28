<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return function (\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) {
    $containerBuilder->register('logger', Logger::class)
        ->setArguments(['app'])
        ->addMethodCall('pushHandler', [new StreamHandler(dirname(__DIR__) . '/../var/logs/app.log', Logger::DEBUG)])
        ->setPublic(true);

    $containerBuilder->setAlias(LoggerInterface::class, 'logger')
        ->setPublic(true);
};
