<?php

use Doctrine\ORM\Tools\Setup;
use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

return function (\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) {
    $containerBuilder->setParameter('doctrine.entity_paths', [
        dirname(__DIR__) . '/src/Entity',
    ]);

    $containerBuilder->setParameter('doctrine.is_dev_mode', true);

    $containerBuilder->setParameter('doctrine.db_params', [
        'driver'   => 'pdo_sqlsrv',
        'host'     => $_ENV["DB_HOST"], 
        'port'     => 1433,
        'user'     => $_ENV["DB_USERNAME"],
        'password' => $_ENV["DB_PASSWORD"],
        'dbname'   => $_ENV["DB_NAME"],
        'options'  => [],
    ]);

    $containerBuilder->register('doctrine.annotation_reader', AnnotationReader::class);

    $containerBuilder->register('doctrine.annotation_driver', AnnotationDriver::class)
        ->setArguments([
            new Reference('doctrine.annotation_reader'),
            '%doctrine.entity_paths%',
        ]);

    $definitionConfig = new Definition(\Doctrine\ORM\Configuration::class);
    $definitionConfig
        ->setFactory([\Doctrine\ORM\Tools\Setup::class, 'createConfiguration'])
        ->setArguments([
            '%doctrine.is_dev_mode%',
        ])
        ->addMethodCall('setMetadataDriverImpl', [new Reference('doctrine.annotation_driver')]);

    $containerBuilder->setDefinition('doctrine.config', $definitionConfig);

    $definitionEm = new Definition(EntityManager::class);
    $definitionEm
        ->setFactory([EntityManager::class, 'create'])
        ->setArguments([
            '%doctrine.db_params%',
            new Reference('doctrine.config'),
        ])
        ->setPublic(true);

    $containerBuilder->setDefinition('entity_manager', $definitionEm);
    $containerBuilder->setAlias(EntityManagerInterface::class, 'entity_manager')
        ->setPublic(true);
    //8) Manager registrery pour manipuler le formulaire
    $containerBuilder->register('manager_registry', SimpleManagerRegistry::class)
    ->setArguments([
        new Reference('entity_manager')
    ])
    ->setPublic(true);

    //9) alias

    $containerBuilder->setAlias(EntityManagerInterface::class, 'entity_manager')
    ->setPublic(true);
};
